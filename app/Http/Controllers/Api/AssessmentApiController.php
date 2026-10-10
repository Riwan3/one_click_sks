<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AssessmentAttemptException;
use App\Http\Controllers\Controller;
use App\Models\ProcedureAssessment;
use App\Models\ProcedureEvaluation;
use App\Services\MyAssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API mobile "Assessment Saya": karyawan mengerjakan assessment yang ditugaskan.
 * Padanan MyAssessmentController (web) dan memakai MyAssessmentService yang sama,
 * jadi aturan lulus, batas percobaan, dan penerbitan sertifikat identik.
 */
class AssessmentApiController extends Controller
{
    public function __construct(private MyAssessmentService $service) {}

    /** Daftar penugasan milik sendiri + ringkasan status. ?status=&search= */
    public function index(Request $request): JsonResponse
    {
        $assignments = $this->service->assignmentsFor($this->nrp($request), $request->only(['search', 'status']));

        return response()->json([
            'stats' => $this->service->stats($assignments),
            'assignments' => $assignments,
        ]);
    }

    /** Materi prosedur sebelum mulai (halaman "Read" di web). */
    public function materi(ProcedureAssessment $assessment, Request $request): JsonResponse
    {
        $nrp = $this->nrp($request);
        $this->service->findAssignment($assessment->id, $nrp);

        if ($this->service->alreadyPassed($assessment->id, $nrp)) {
            return $this->tolak('Anda sudah LULUS untuk assessment ini, tidak perlu mengulang.');
        }

        $attemptCount = $this->service->attemptCount($assessment->id, $nrp);
        $effectiveRetryLimit = $assessment->retry_limit + $this->service->extraAttempts($assessment->id, $nrp);
        if ($assessment->retry_limit < 99 && $attemptCount >= $effectiveRetryLimit) {
            return $this->tolak('Batas percobaan habis.');
        }

        $assessment->load('procedure');
        $procedure = $assessment->procedure;

        return response()->json([
            'assessment' => [
                'id' => $assessment->id,
                'title' => $assessment->title,
                'passing_grade' => $assessment->passing_grade,
                'timer_minutes' => $assessment->timer_minutes,
                'attempt_number' => $attemptCount + 1,
                'effective_retry_limit' => $effectiveRetryLimit,
            ],
            'procedure' => [
                'title' => $procedure?->title,
                'description' => $procedure?->description,
                'risk_level' => $procedure?->risk_level,
                'effective_date' => $procedure?->effective_date?->toDateString(),
                'file_url' => $procedure?->file_path ? asset('storage/'.$procedure->file_path) : null,
                'file_type' => $procedure?->file_type,
            ],
        ]);
    }

    /** Mulai attempt baru atau lanjutkan yang sedang berjalan; mengembalikan soal. */
    public function start(ProcedureAssessment $assessment, Request $request): JsonResponse
    {
        try {
            $evaluation = $this->service->startOrResume($assessment, $this->nrp($request));
        } catch (AssessmentAttemptException $e) {
            return $this->tolak($e->getMessage());
        }

        return response()->json($this->kuis($evaluation));
    }

    /** Lanjutkan kuis (mis. setelah app ditutup); bila sudah selesai, 409 + id untuk lihat hasil. */
    public function quiz(ProcedureEvaluation $evaluation, Request $request): JsonResponse
    {
        $this->pastikanMilik($evaluation, $request);

        if (! $evaluation->isInProgress()) {
            return $this->tolak('Assessment ini sudah diselesaikan.', ['evaluation_id' => $evaluation->id]);
        }

        return response()->json($this->kuis($evaluation));
    }

    public function autosave(ProcedureEvaluation $evaluation, Request $request): JsonResponse
    {
        $this->pastikanMilik($evaluation, $request);

        $data = $request->validate(['answers' => ['nullable', 'array']]);
        $this->service->autosave($evaluation, $data['answers'] ?? []);

        return response()->json(['ok' => true]);
    }

    /** Kirim jawaban: waktu pengerjaan dihitung server dari started_at, bukan dari kiriman klien. */
    public function submit(ProcedureEvaluation $evaluation, Request $request): JsonResponse
    {
        $this->pastikanMilik($evaluation, $request);

        $data = $request->validate(['answers' => ['nullable', 'array']]);
        $timeTaken = max(0, (int) $evaluation->started_at?->diffInSeconds(now(), true));

        $this->service->submit($evaluation, $data['answers'] ?? [], $timeTaken);

        return response()->json($this->service->resultDataFor($evaluation->fresh()));
    }

    public function result(ProcedureEvaluation $evaluation, Request $request): JsonResponse
    {
        $this->pastikanMilik($evaluation, $request);

        return response()->json($this->service->resultDataFor($evaluation));
    }

    /** Soal (tanpa kunci jawaban) + jawaban tersimpan + sisa waktu menurut server. */
    private function kuis(ProcedureEvaluation $evaluation): array
    {
        $evaluation->loadMissing('assessment.procedure');
        $assessment = $evaluation->assessment;
        $saved = $this->service->savedAnswersFor($evaluation);
        $totalDetik = (int) $assessment->timer_minutes * 60;
        $berjalan = max(0, (int) $evaluation->started_at?->diffInSeconds(now(), true));

        return [
            'evaluation' => [
                'id' => $evaluation->id,
                'procedure_title' => $assessment->procedure?->title,
                'timer_minutes' => $assessment->timer_minutes,
                'seconds_left' => max(0, $totalDetik - $berjalan),
            ],
            'questions' => $this->service->questionsFor($assessment)->map(fn ($q) => [
                'id' => $q->id,
                'question_type' => $q->question_type,
                'question' => $q->question,
                'option_a' => $q->option_a,
                'option_b' => $q->option_b,
                'option_c' => $q->option_c,
                'option_d' => $q->option_d,
                'saved_answer' => $saved[$q->id] ?? '',
            ])->values(),
        ];
    }

    private function tolak(string $pesan, array $extra = []): JsonResponse
    {
        return response()->json(['message' => $pesan] + $extra, 409);
    }

    private function pastikanMilik(ProcedureEvaluation $evaluation, Request $request): void
    {
        abort_unless($evaluation->nrp === $this->nrp($request), 403);
    }

    private function nrp(Request $request): string
    {
        $user = $request->user()?->loadMissing('employee');

        return (string) ($user?->employee?->nrp ?: $user?->username);
    }
}
