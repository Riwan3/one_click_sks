<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\Employee;
use App\Models\Procedure;
use App\Models\ProcedureAssessment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ProcedureAssessmentTest extends TestCase
{
    use DatabaseTransactions;

    private const BASE = '/one-procedure/evaluasi/assessment';

    private Procedure $procedure;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
        $this->procedure = Procedure::create(['title' => 'SOP Test Assessment', 'risk_level' => 'medium']);
    }

    private function manager(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'tass'.random_int(1000, 9999)]);
    }

    private function makeAssessment(array $attributes = []): ProcedureAssessment
    {
        return ProcedureAssessment::create([
            'procedure_id' => $this->procedure->id,
            'title' => 'Assessment Test',
            'status' => 'draft',
            'target_roles' => [],
            ...$attributes,
        ]);
    }

    public function test_manager_can_create_assessment_and_is_redirected_to_manage(): void
    {
        $response = $this->actingAs($this->manager())->post(self::BASE, [
            'procedure_id' => $this->procedure->id,
            'title' => 'Assessment Baru Test',
            'validity_months' => 0,
            'randomize_questions' => true,
        ]);

        $assessment = ProcedureAssessment::where('title', 'Assessment Baru Test')->firstOrFail();
        $response->assertRedirect(route('one-procedure.evaluasi', ['tab' => 'assessment', 'manage' => $assessment->id]));
        $this->assertSame(0, $assessment->validity_months);
        $this->assertTrue($assessment->randomize_questions);
        $this->assertSame(75, $assessment->passing_grade);
    }

    public function test_publish_assigns_employees_matching_target_role_pattern(): void
    {
        Employee::create(['nrp' => 'TASS001', 'name' => 'Operator Test', 'jabatan_ss6' => 'OPERATOR ZETATEST 99']);
        $assessment = $this->makeAssessment(['target_roles' => ['ZETATEST']]);

        $this->actingAs($this->manager())->post(self::BASE."/{$assessment->id}/publish")
            ->assertSessionHas('success', 'Assessment dipublish, 1 notifikasi dikirim');

        $this->assertSame('published', $assessment->fresh()->status);
        $this->assertDatabaseHas('procedure_assignments', ['assessment_id' => $assessment->id, 'nrp' => 'TASS001']);
    }

    public function test_assessment_tab_renders_list_and_manage_view(): void
    {
        $assessment = $this->makeAssessment(['validity_months' => 0]);
        $user = $this->manager();

        $this->actingAs($user)->get('/one-procedure/evaluasi?tab=assessment')->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->has('assessment.list')->where('assessment.manage', null));

        $this->actingAs($user)->get("/one-procedure/evaluasi?tab=assessment&manage={$assessment->id}")->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('assessment.manage.validity_months', 0)
                ->where('assessment.stats.total_questions', 0)
                ->has('assessment.targetRoles'));
    }

    public function test_true_false_question_stores_fixed_options(): void
    {
        $assessment = $this->makeAssessment();

        $this->actingAs($this->manager())->post(self::BASE."/{$assessment->id}/questions", [
            'question_type' => 'true_false',
            'question' => 'APD wajib dipakai di area tambang.',
            'correct' => 'A',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('procedure_questions', [
            'assessment_id' => $assessment->id, 'option_a' => 'Benar', 'option_b' => 'Salah', 'option_c' => null, 'correct' => 'A',
        ]);
    }

    public function test_question_requires_correct_answer(): void
    {
        $assessment = $this->makeAssessment();

        $this->actingAs($this->manager())
            ->post(self::BASE."/{$assessment->id}/questions", ['question_type' => 'multiple_choice', 'question' => 'Soal?', 'option_a' => 'a', 'option_b' => 'b'])
            ->assertSessionHasErrors(['correct' => 'Pilih jawaban benar terlebih dahulu']);
    }

    public function test_operator_cannot_manage_assessment(): void
    {
        $operator = User::factory()->create(['role' => 'operator', 'username' => 'tassop'.random_int(1000, 9999)]);
        $assessment = $this->makeAssessment();

        $this->actingAs($operator)->post(self::BASE, ['procedure_id' => $this->procedure->id, 'title' => 'X'])->assertForbidden();
        $this->actingAs($operator)->post(self::BASE."/{$assessment->id}/publish")->assertForbidden();
        $this->actingAs($operator)->delete(self::BASE."/{$assessment->id}")->assertForbidden();
    }

    public function test_only_assigned_signer_can_sign(): void
    {
        $signer = Employee::create(['nrp' => 'TASSSH1', 'name' => 'Section Head Test', 'jabatan_ss6' => 'SECTION HEAD PRODUKSI']);
        $assessment = $this->makeAssessment(['signed_by' => $signer->id]);
        $signerUser = User::factory()->create(['role' => 'pimpinan', 'username' => 'tasssig'.random_int(1000, 9999), 'employee_id' => $signer->id]);

        $this->actingAs($this->manager())->post(self::BASE."/{$assessment->id}/sign", ['signature' => 'data:image/png;base64,AAAA'])->assertForbidden();
        $this->actingAs($signerUser)->post(self::BASE."/{$assessment->id}/sign", ['signature' => 'data:image/png;base64,AAAA'])->assertSessionHas('success');

        $this->assertNotNull($assessment->fresh()->signer_signed_at);
    }
}
