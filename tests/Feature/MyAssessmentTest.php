<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\Employee;
use App\Models\Procedure;
use App\Models\ProcedureAssessment;
use App\Models\ProcedureAssignment;
use App\Models\ProcedureEvaluation;
use App\Models\ProcedureQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class MyAssessmentTest extends TestCase
{
    use DatabaseTransactions;

    private const BASE = '/one-procedure/assessment';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
    }

    private function makeAssessment(array $attributes = []): ProcedureAssessment
    {
        $procedure = Procedure::create(['title' => 'SOP Test MyAssessment', 'risk_level' => 'medium']);

        return ProcedureAssessment::create([
            'procedure_id' => $procedure->id,
            'title' => 'Assessment Test',
            'passing_grade' => 75,
            'timer_minutes' => 30,
            'retry_limit' => 2,
            'status' => 'published',
            'target_roles' => [],
            ...$attributes,
        ]);
    }

    private function assignedUser(ProcedureAssessment $assessment, string $nrp): User
    {
        $employee = Employee::create(['nrp' => $nrp, 'name' => 'Operator '.$nrp, 'jabatan_ss6' => 'OPERATOR TEST']);
        ProcedureAssignment::create(['assessment_id' => $assessment->id, 'nrp' => $nrp]);

        return User::factory()->create(['role' => 'operator', 'username' => $nrp, 'employee_id' => $employee->id]);
    }

    private function addQuestion(ProcedureAssessment $assessment, string $correct = 'A'): ProcedureQuestion
    {
        return $assessment->questions()->create([
            'question_type' => 'multiple_choice',
            'question' => 'Apa jawaban yang benar?',
            'option_a' => 'Benar',
            'option_b' => 'Salah',
            'correct' => $correct,
        ]);
    }

    public function test_assigned_employee_can_complete_full_flow_and_pass(): void
    {
        $assessment = $this->makeAssessment();
        $question = $this->addQuestion($assessment, 'A');
        $user = $this->assignedUser($assessment, 'MYA001');

        $this->actingAs($user)->get(self::BASE."/{$assessment->id}/read")->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('assessment.attempt_number', 1));

        $startResponse = $this->actingAs($user)->post(self::BASE."/{$assessment->id}/start");
        $evaluation = ProcedureEvaluation::where('assessment_id', $assessment->id)->where('nrp', 'MYA001')->firstOrFail();
        $startResponse->assertRedirect(route('one-procedure.my-assessment.quiz', $evaluation));
        $this->assertSame('in_progress', $evaluation->status);

        $this->actingAs($user)->get(route('one-procedure.my-assessment.quiz', $evaluation))->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->has('questions', 1));

        $this->actingAs($user)->post(route('one-procedure.my-assessment.autosave', $evaluation), [
            'answers' => [$question->id => 'A'],
        ])->assertOk()->assertJson(['ok' => true]);

        $submitResponse = $this->actingAs($user)->post(route('one-procedure.my-assessment.submit', $evaluation), [
            'answers' => [$question->id => 'A'],
            'time_taken' => 60,
        ]);
        $submitResponse->assertRedirect(route('one-procedure.my-assessment.result', $evaluation));

        $evaluation->refresh();
        $this->assertSame('passed', $evaluation->status);
        $this->assertSame(100, $evaluation->score);
        $this->assertDatabaseHas('procedure_certificates', ['assessment_id' => $assessment->id, 'nrp' => 'MYA001']);

        $this->actingAs($user)->get(route('one-procedure.my-assessment.result', $evaluation))->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('result.passed', true)->where('result.score', 100));

        // Sudah lulus: tidak boleh mengulang lagi
        $this->actingAs($user)->get(self::BASE."/{$assessment->id}/read")->assertRedirect(route('one-procedure.my-assessment.index'));
    }

    public function test_unassigned_employee_cannot_read_or_start(): void
    {
        $assessment = $this->makeAssessment();
        $outsider = User::factory()->create(['role' => 'operator', 'username' => 'MYAOUT']);
        Employee::create(['nrp' => 'MYAOUT', 'name' => 'Outsider']);

        $this->actingAs($outsider)->get(self::BASE."/{$assessment->id}/read")->assertNotFound();
        $this->actingAs($outsider)->post(self::BASE."/{$assessment->id}/start")->assertNotFound();
    }

    public function test_employee_cannot_access_another_employees_evaluation(): void
    {
        $assessment = $this->makeAssessment();
        $this->addQuestion($assessment);
        $owner = $this->assignedUser($assessment, 'MYA010');
        $this->actingAs($owner)->post(self::BASE."/{$assessment->id}/start");
        $evaluation = ProcedureEvaluation::where('nrp', 'MYA010')->firstOrFail();

        $intruder = User::factory()->create(['role' => 'operator', 'username' => 'MYA011']);
        Employee::create(['nrp' => 'MYA011', 'name' => 'Intruder']);

        $this->actingAs($intruder)->get(route('one-procedure.my-assessment.quiz', $evaluation))->assertForbidden();
        $this->actingAs($intruder)->post(route('one-procedure.my-assessment.submit', $evaluation), ['time_taken' => 1])->assertForbidden();
    }

    public function test_failed_attempt_can_retake_until_retry_limit_then_blocked(): void
    {
        $assessment = $this->makeAssessment(['retry_limit' => 1]);
        $question = $this->addQuestion($assessment, 'A');
        $user = $this->assignedUser($assessment, 'MYA020');

        // Attempt 1: gagal
        $this->actingAs($user)->post(self::BASE."/{$assessment->id}/start");
        $evaluation = ProcedureEvaluation::where('nrp', 'MYA020')->firstOrFail();
        $this->actingAs($user)->post(route('one-procedure.my-assessment.submit', $evaluation), [
            'answers' => [$question->id => 'B'],
            'time_taken' => 30,
        ]);
        $this->assertSame('failed', $evaluation->fresh()->status);

        // retry_limit sudah habis (1) -> tidak boleh mulai lagi
        $this->actingAs($user)->get(self::BASE."/{$assessment->id}/read")->assertRedirect(route('one-procedure.my-assessment.index'));
        $this->actingAs($user)->post(self::BASE."/{$assessment->id}/start")->assertRedirect(route('one-procedure.my-assessment.index'));
        $this->assertSame(1, ProcedureEvaluation::where('nrp', 'MYA020')->count());
    }

    public function test_starting_twice_resumes_same_in_progress_evaluation(): void
    {
        $assessment = $this->makeAssessment();
        $this->addQuestion($assessment);
        $user = $this->assignedUser($assessment, 'MYA030');

        $this->actingAs($user)->post(self::BASE."/{$assessment->id}/start");
        $this->actingAs($user)->post(self::BASE."/{$assessment->id}/start");

        $this->assertSame(1, ProcedureEvaluation::where('assessment_id', $assessment->id)->where('nrp', 'MYA030')->count());
    }

    public function test_index_lists_assignment_with_correct_status(): void
    {
        $assessment = $this->makeAssessment();
        $this->addQuestion($assessment);
        $user = $this->assignedUser($assessment, 'MYA040');

        $this->actingAs($user)->get(self::BASE)->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('assignments.0.status', 'pending')
                ->where('assignments.0.can_start', true)
                ->where('stats.pending', 1));
    }
}
