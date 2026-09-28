<?php

namespace App\Services;

use App\Enums\ProcedureCertificateStatus;
use App\Exceptions\AssessmentAttemptException;
use App\Models\ProcedureAnswer;
use App\Models\ProcedureAssessment;
use App\Models\ProcedureAssignment;
use App\Models\ProcedureCertificate;
use App\Models\ProcedureEvaluation;
use App\Models\ProcedureFollowup;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Assessment Saya: alur karyawan mengerjakan assessment yang ditugaskan (padanan /assessment/*.php di v1). */
class MyAssessmentService
{
    public function __construct(private ProcedureCertificateService $certificateService) {}

    /** Daftar assessment yang ditugaskan ke NRP ini + status pengerjaan terakhir. */
    public function assignmentsFor(string $nrp, array $filters = []): Collection
    {
        $assignments = ProcedureAssignment::query()
            ->with(['assessment.procedure'])
            ->whereHas('assessment', fn ($q) => $q->whereIn('status', ['published', 'archived']))
            ->where('nrp', $nrp)
            ->get();

        $evaluationsByAssessment = ProcedureEvaluation::where('nrp', $nrp)
            ->whereIn('assessment_id', $assignments->pluck('assessment_id'))
            ->orderByDesc('id')
            ->get()
            ->groupBy('assessment_id');

        $extraAttemptsByAssessment = ProcedureFollowup::where('nrp', $nrp)
            ->whereNotNull('remedial_granted_at')
            ->whereIn('assessment_id', $assignments->pluck('assessment_id'))
            ->get()
            ->groupBy('assessment_id')
            ->map->count();

        $certificatesByAssessment = ProcedureCertificate::where('nrp', $nrp)
            ->where('status', '!=', ProcedureCertificateStatus::Revoked)
            ->whereIn('assessment_id', $assignments->pluck('assessment_id'))
            ->get()
            ->groupBy('assessment_id');

        $rows = $assignments->map(function (ProcedureAssignment $assignment) use ($evaluationsByAssessment, $extraAttemptsByAssessment, $certificatesByAssessment) {
            $evaluations = $evaluationsByAssessment->get($assignment->assessment_id, collect());
            $latest = $evaluations->first();
            $completed = $evaluations->whereNotIn('status', ['in_progress']);
            $passed = $completed->firstWhere('status', 'passed');
            $extraAttempts = $extraAttemptsByAssessment->get($assignment->assessment_id, 0);
            $assessment = $assignment->assessment;
            $effectiveRetryLimit = $assessment->retry_limit + $extraAttempts;
            $canRetake = ! $passed && $latest && $latest->status !== 'in_progress'
                && ($completed->count() < $effectiveRetryLimit || $assessment->retry_limit >= 99);

            return [
                'assessment_id' => $assignment->assessment_id,
                'assessment_title' => $assessment->title,
                'procedure_title' => $assessment->procedure?->title,
                'risk_level' => $assessment->procedure?->risk_level,
                'passing_grade' => $assessment->passing_grade,
                'timer_minutes' => $assessment->timer_minutes,
                'retry_limit' => $assessment->retry_limit,
                'effective_retry_limit' => $effectiveRetryLimit,
                'total_attempts' => $completed->count(),
                'sent_at' => $assignment->created_at?->toDateString(),
                'status' => match (true) {
                    $latest === null => 'pending',
                    $latest->status === 'in_progress' => 'in_progress',
                    $latest->status === 'passed' => 'passed',
                    default => 'failed',
                },
                'score' => $latest && $latest->status !== 'in_progress' ? $latest->score : null,
                'evaluation_id' => $latest?->id,
                'can_start' => $latest === null,
                'can_continue' => $latest?->status === 'in_progress',
                'can_retake' => $canRetake,
                'certificate_code' => $certificatesByAssessment->get($assignment->assessment_id, collect())->first()?->certificate_code,
            ];
        });

        return $this->applyFilters($rows, $filters);
    }

    private function applyFilters(Collection $rows, array $filters): Collection
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        return $rows
            ->when($search !== '', fn ($r) => $r->filter(fn ($a) => str_contains(strtolower($a['procedure_title'].$a['assessment_title']), strtolower($search))))
            ->when($status !== '', fn ($r) => $r->filter(fn ($a) => $a['status'] === $status))
            ->values();
    }

    public function stats(Collection $assignments): array
    {
        return [
            'pending' => $assignments->where('status', 'pending')->count(),
            'in_progress' => $assignments->where('status', 'in_progress')->count(),
            'passed' => $assignments->where('status', 'passed')->count(),
            'failed' => $assignments->where('status', 'failed')->count(),
            'certs' => $assignments->whereNotNull('certificate_code')->count(),
        ];
    }

    public function findAssignment(int $assessmentId, string $nrp): ProcedureAssignment
    {
        return ProcedureAssignment::where('assessment_id', $assessmentId)->where('nrp', $nrp)->firstOrFail();
    }

    public function attemptCount(int $assessmentId, string $nrp): int
    {
        return ProcedureEvaluation::where('assessment_id', $assessmentId)->where('nrp', $nrp)->count();
    }

    public function extraAttempts(int $assessmentId, string $nrp): int
    {
        return ProcedureFollowup::where('assessment_id', $assessmentId)->where('nrp', $nrp)->whereNotNull('remedial_granted_at')->count();
    }

    public function alreadyPassed(int $assessmentId, string $nrp): bool
    {
        return ProcedureEvaluation::where('assessment_id', $assessmentId)->where('nrp', $nrp)->where('status', 'passed')->exists();
    }

    /** Cari attempt in_progress, atau buat baru bila masih ada jatah percobaan. */
    public function startOrResume(ProcedureAssessment $assessment, string $nrp): ProcedureEvaluation
    {
        $this->findAssignment($assessment->id, $nrp);

        if ($this->alreadyPassed($assessment->id, $nrp)) {
            throw new AssessmentAttemptException('Anda sudah LULUS untuk assessment ini, tidak perlu mengulang.');
        }

        $existing = ProcedureEvaluation::where('assessment_id', $assessment->id)
            ->where('nrp', $nrp)
            ->where('status', 'in_progress')
            ->latest('id')
            ->first();
        if ($existing) {
            return $existing;
        }

        $attemptNumber = $this->attemptCount($assessment->id, $nrp) + 1;
        $extraAttempts = $this->extraAttempts($assessment->id, $nrp);

        if ($assessment->retry_limit < 99 && $attemptNumber > $assessment->retry_limit + $extraAttempts) {
            throw new AssessmentAttemptException('Batas percobaan habis.');
        }

        return ProcedureEvaluation::create([
            'assessment_id' => $assessment->id,
            'nrp' => $nrp,
            'attempt' => $attemptNumber,
            'status' => 'in_progress',
            'started_at' => now(),
            'score' => 0,
        ]);
    }

    /** Soal untuk assessment, diacak bila randomize_questions aktif. */
    public function questionsFor(ProcedureAssessment $assessment): EloquentCollection
    {
        $questions = $assessment->questions()->orderBy('id')->get();

        return $assessment->randomize_questions ? $questions->shuffle()->values() : $questions;
    }

    /** Jawaban tersimpan (autosave / lanjutan) sebagai [question_id => huruf]. */
    public function savedAnswersFor(ProcedureEvaluation $evaluation): array
    {
        return $evaluation->answers()->pluck('user_answer', 'question_id')->all();
    }

    public function autosave(ProcedureEvaluation $evaluation, array $answers): void
    {
        if (! $evaluation->isInProgress()) {
            return;
        }

        foreach ($answers as $questionId => $answer) {
            $answer = strtoupper(trim((string) $answer));
            if ($answer === '') {
                continue;
            }

            ProcedureAnswer::updateOrCreate(
                ['evaluation_id' => $evaluation->id, 'question_id' => (int) $questionId],
                ['user_answer' => $answer]
            );
        }
    }

    /** Hitung skor, tandai lulus/gagal, dan terbitkan sertifikat otomatis bila lulus. */
    public function submit(ProcedureEvaluation $evaluation, array $answers, int $timeTaken): ProcedureEvaluation
    {
        abort_unless($evaluation->isInProgress(), 409, 'Assessment ini sudah diselesaikan.');

        $questions = $evaluation->assessment->questions;
        $passingGrade = $evaluation->assessment->passing_grade;

        DB::transaction(function () use ($evaluation, $answers, $questions, $timeTaken, $passingGrade) {
            ProcedureAnswer::where('evaluation_id', $evaluation->id)->delete();

            $correct = 0;
            foreach ($questions as $question) {
                $userAnswer = strtoupper(trim((string) ($answers[$question->id] ?? '')));
                $isCorrect = $userAnswer !== '' && $userAnswer === strtoupper(trim($question->correct));
                $isCorrect and $correct++;

                ProcedureAnswer::create([
                    'evaluation_id' => $evaluation->id,
                    'question_id' => $question->id,
                    'user_answer' => $userAnswer ?: null,
                    'is_correct' => $isCorrect,
                ]);
            }

            $total = $questions->count();
            $score = $total > 0 ? (int) round(($correct / $total) * 100) : 0;
            $passed = $score >= $passingGrade;

            $evaluation->update([
                'status' => $passed ? 'passed' : 'failed',
                'score' => $score,
                'completed_at' => now(),
                'time_taken' => $timeTaken,
            ]);

            $passed and $this->certificateService->issue($evaluation->fresh(), 'system');
        });

        return $evaluation->fresh();
    }

    /** Data lengkap untuk halaman hasil: skor, review jawaban, sertifikat, & kelayakan retake. */
    public function resultDataFor(ProcedureEvaluation $evaluation): array
    {
        $evaluation->loadMissing(['assessment.procedure', 'answers.question']);

        $totalAttempts = $this->attemptCount($evaluation->assessment_id, $evaluation->nrp);
        $extraAttempts = $this->extraAttempts($evaluation->assessment_id, $evaluation->nrp);
        $effectiveRetryLimit = $evaluation->assessment->retry_limit + $extraAttempts;
        $passed = $evaluation->isPassed();
        $canRetake = ! $passed && ($totalAttempts < $effectiveRetryLimit || $evaluation->assessment->retry_limit >= 99);

        $certificate = $passed
            ? ProcedureCertificate::where('assessment_id', $evaluation->assessment_id)->where('nrp', $evaluation->nrp)
                ->where('status', '!=', ProcedureCertificateStatus::Revoked)->latest('id')->first()
            : null;

        $correctCount = $evaluation->answers->where('is_correct', true)->count();
        $totalQuestions = $evaluation->answers->count();

        return [
            'evaluation_id' => $evaluation->id,
            'assessment_id' => $evaluation->assessment_id,
            'procedure_title' => $evaluation->assessment->procedure?->title,
            'assessment_title' => $evaluation->assessment->title,
            'passing_grade' => $evaluation->assessment->passing_grade,
            'score' => $evaluation->score,
            'passed' => $passed,
            'time_taken' => $evaluation->time_taken,
            'correct_count' => $correctCount,
            'wrong_count' => $totalQuestions - $correctCount,
            'total_questions' => $totalQuestions,
            'total_attempts' => $totalAttempts,
            'effective_retry_limit' => $effectiveRetryLimit,
            'retry_limit' => $evaluation->assessment->retry_limit,
            'can_retake' => $canRetake,
            'certificate_code' => $certificate?->certificate_code,
            'answers' => $evaluation->answers->map(fn (ProcedureAnswer $a) => [
                'question' => $a->question->question,
                'question_type' => $a->question->question_type,
                'option_a' => $a->question->option_a,
                'option_b' => $a->question->option_b,
                'option_c' => $a->question->option_c,
                'option_d' => $a->question->option_d,
                'correct_answer' => $a->question->correct,
                'user_answer' => $a->user_answer,
                'is_correct' => $a->is_correct,
                'explanation' => $a->question->explanation,
            ])->values(),
        ];
    }
}
