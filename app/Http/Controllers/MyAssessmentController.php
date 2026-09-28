<?php

namespace App\Http\Controllers;

use App\Exceptions\AssessmentAttemptException;
use App\Models\ProcedureAssessment;
use App\Models\ProcedureEvaluation;
use App\Services\MyAssessmentService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Assessment Saya: karyawan mengerjakan assessment yang ditugaskan (padanan /assessment/*.php di v1). */
class MyAssessmentController extends Controller
{
    public function __construct(private MyAssessmentService $service) {}

    public function index(Request $request): Response
    {
        $nrp = $this->currentNrp($request);
        $assignments = $this->service->assignmentsFor($nrp, $request->only(['search', 'status']));

        return Inertia::render('OneProcedure/MyAssessment/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_procedure_menu'),
            'filters' => $request->only(['search', 'status']),
            'stats' => $this->service->stats($assignments),
            'assignments' => $assignments,
        ]);
    }

    public function read(ProcedureAssessment $assessment, Request $request): Response|RedirectResponse
    {
        $nrp = $this->currentNrp($request);
        $this->service->findAssignment($assessment->id, $nrp);

        if ($this->service->alreadyPassed($assessment->id, $nrp)) {
            return redirect()->route('one-procedure.my-assessment.index')->with('error', 'Anda sudah LULUS untuk assessment ini, tidak perlu mengulang.');
        }

        $attemptCount = $this->service->attemptCount($assessment->id, $nrp);
        $extraAttempts = $this->service->extraAttempts($assessment->id, $nrp);
        $effectiveRetryLimit = $assessment->retry_limit + $extraAttempts;

        if ($assessment->retry_limit < 99 && $attemptCount >= $effectiveRetryLimit) {
            return redirect()->route('one-procedure.my-assessment.index')->with('error', 'Batas percobaan habis.');
        }

        $assessment->load('procedure');

        return Inertia::render('OneProcedure/MyAssessment/Read', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_procedure_menu'),
            'assessment' => [
                'id' => $assessment->id,
                'title' => $assessment->title,
                'passing_grade' => $assessment->passing_grade,
                'timer_minutes' => $assessment->timer_minutes,
                'attempt_number' => $attemptCount + 1,
                'effective_retry_limit' => $effectiveRetryLimit,
            ],
            'procedure' => [
                'title' => $assessment->procedure->title,
                'description' => $assessment->procedure->description,
                'risk_level' => $assessment->procedure->risk_level,
                'effective_date' => $assessment->procedure->effective_date?->toDateString(),
                'file_path' => $assessment->procedure->file_path ? asset('storage/'.$assessment->procedure->file_path) : null,
                'file_type' => $assessment->procedure->file_type,
            ],
        ]);
    }

    public function start(ProcedureAssessment $assessment, Request $request): RedirectResponse
    {
        $nrp = $this->currentNrp($request);

        try {
            $evaluation = $this->service->startOrResume($assessment, $nrp);
        } catch (AssessmentAttemptException $e) {
            return redirect()->route('one-procedure.my-assessment.index')->with('error', $e->getMessage());
        }

        return redirect()->route('one-procedure.my-assessment.quiz', $evaluation);
    }

    public function quiz(ProcedureEvaluation $evaluation, Request $request): Response|RedirectResponse
    {
        $this->authorizeOwnership($evaluation, $request);

        if (! $evaluation->isInProgress()) {
            return redirect()->route('one-procedure.my-assessment.result', $evaluation);
        }

        $evaluation->load('assessment.procedure');
        $questions = $this->service->questionsFor($evaluation->assessment);
        $savedAnswers = $this->service->savedAnswersFor($evaluation);

        return Inertia::render('OneProcedure/MyAssessment/Quiz', [
            'evaluation' => [
                'id' => $evaluation->id,
                'procedure_title' => $evaluation->assessment->procedure?->title,
                'timer_minutes' => $evaluation->assessment->timer_minutes,
            ],
            'questions' => $questions->map(fn ($q) => [
                'id' => $q->id,
                'question_type' => $q->question_type,
                'question' => $q->question,
                'option_a' => $q->option_a,
                'option_b' => $q->option_b,
                'option_c' => $q->option_c,
                'option_d' => $q->option_d,
                'saved_answer' => $savedAnswers[$q->id] ?? '',
            ])->values(),
        ]);
    }

    public function autosave(ProcedureEvaluation $evaluation, Request $request): JsonResponse
    {
        $this->authorizeOwnership($evaluation, $request);

        $data = $request->validate(['answers' => ['nullable', 'array']]);
        $this->service->autosave($evaluation, $data['answers'] ?? []);

        return response()->json(['ok' => true]);
    }

    public function submit(ProcedureEvaluation $evaluation, Request $request): RedirectResponse
    {
        $this->authorizeOwnership($evaluation, $request);

        $data = $request->validate([
            'answers' => ['nullable', 'array'],
            'time_taken' => ['required', 'integer', 'min:0'],
        ]);

        $this->service->submit($evaluation, $data['answers'] ?? [], $data['time_taken']);

        return redirect()->route('one-procedure.my-assessment.result', $evaluation);
    }

    public function result(ProcedureEvaluation $evaluation, Request $request): Response
    {
        $this->authorizeOwnership($evaluation, $request);

        return Inertia::render('OneProcedure/MyAssessment/Result', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_procedure_menu'),
            'result' => $this->service->resultDataFor($evaluation),
        ]);
    }

    private function authorizeOwnership(ProcedureEvaluation $evaluation, Request $request): void
    {
        abort_unless($evaluation->nrp === $this->currentNrp($request), 403);
    }

    private function currentNrp(Request $request): string
    {
        $user = $request->user()?->loadMissing('employee');

        return (string) ($user?->employee?->nrp ?: $user?->username);
    }
}
