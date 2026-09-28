<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\Employee;
use App\Models\Procedure;
use App\Models\ProcedureAssessment;
use App\Models\ProcedureEvaluation;
use App\Models\ProcedureFollowup;
use App\Models\User;
use App\Services\ProcedureMonitoringService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ProcedureMonitoringTest extends TestCase
{
    use DatabaseTransactions;

    private const BASE = '/one-procedure/evaluasi/monitoring';

    private ProcedureAssessment $assessment;

    private ProcedureEvaluation $failedEvaluation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);

        $procedure = Procedure::create(['title' => 'SOP Test Monitoring', 'risk_level' => 'high']);
        $this->assessment = ProcedureAssessment::create([
            'procedure_id' => $procedure->id,
            'title' => 'Assessment Test Monitoring',
            'passing_grade' => 80,
            'status' => 'published',
            'target_roles' => [],
        ]);
        $this->failedEvaluation = ProcedureEvaluation::create([
            'assessment_id' => $this->assessment->id,
            'nrp' => 'TMON001',
            'score' => 60,
            'status' => 'failed',
            'completed_at' => now(),
        ]);
        app(ProcedureMonitoringService::class)->assignEmployee($this->assessment->id, 'TMON001');
    }

    private function manager(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'tmon'.random_int(1000, 9999)]);
    }

    public function test_monitoring_tab_lists_assigned_employee_with_status(): void
    {
        $this->actingAs($this->manager())
            ->get('/one-procedure/evaluasi?tab=monitoring&assessment_id='.$this->assessment->id)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('monitoring.summary.total_assigned', 1)
                ->where('monitoring.summary.failed', 1)
                ->where('monitoring.rows.0.nrp', 'TMON001')
                ->where('monitoring.rows.0.status', 'FAILED'));
    }

    public function test_manager_can_grant_remedial_and_status_becomes_remedial(): void
    {
        $user = $this->manager();

        $this->actingAs($user)->post(self::BASE."/{$this->failedEvaluation->id}/remedial")->assertRedirect();

        $this->assertDatabaseHas('procedure_followups', ['nrp' => 'TMON001', 'type' => 'remedial_only']);
        $this->actingAs($user)
            ->get('/one-procedure/evaluasi?tab=monitoring&status=REMEDIAL&assessment_id='.$this->assessment->id)
            ->assertInertia(fn (AssertableInertia $page) => $page->where('monitoring.pagination.total', 1));
    }

    public function test_coaching_then_grant_remedial_flow(): void
    {
        $user = $this->manager();
        $mentor = Employee::create(['nrp' => 'TMONGL1', 'name' => 'Mentor GL', 'jabatan_ss6' => 'GROUP LEADER PRODUKSI']);

        $this->actingAs($user)->post(self::BASE."/{$this->failedEvaluation->id}/coaching", [
            'mentor_id' => $mentor->id,
            'coaching_target_date' => now()->addWeek()->toDateString(),
        ])->assertSessionHasNoErrors();

        $followup = ProcedureFollowup::where('nrp', 'TMON001')->firstOrFail();
        $this->assertNull($followup->remedial_granted_at);

        $this->actingAs($user)->post(self::BASE."/followup/{$followup->id}/grant")->assertSessionHas('success');
        $this->assertNotNull($followup->fresh()->remedial_granted_at);
    }

    public function test_coaching_requires_mentor_and_target_date(): void
    {
        $this->actingAs($this->manager())
            ->post(self::BASE."/{$this->failedEvaluation->id}/coaching", [])
            ->assertSessionHasErrors(['mentor_id', 'coaching_target_date']);
    }

    public function test_operator_cannot_manage_followup(): void
    {
        $operator = User::factory()->create(['role' => 'operator', 'username' => 'tmonop'.random_int(1000, 9999)]);

        $this->actingAs($operator)->post(self::BASE."/{$this->failedEvaluation->id}/remedial")->assertForbidden();
        $this->actingAs($operator)->delete(self::BASE."/{$this->assessment->id}/TMON001")->assertForbidden();
    }

    public function test_detail_returns_result_and_history(): void
    {
        $this->actingAs($this->manager())
            ->getJson(self::BASE."/{$this->failedEvaluation->id}")
            ->assertOk()
            ->assertJsonPath('result.nrp', 'TMON001')
            ->assertJsonPath('result.passed', false)
            ->assertJsonCount(1, 'history');
    }

    public function test_exports_contain_filtered_rows(): void
    {
        $user = $this->manager();
        $query = '?assessment_id='.$this->assessment->id;

        $excel = $this->actingAs($user)->get(self::BASE.'/export/excel'.$query)->assertOk();
        $this->assertStringContainsString('TMON001', $excel->streamedContent());

        $this->actingAs($user)->get(self::BASE.'/export/pdf'.$query)->assertOk()->assertSee('TMON001');
    }

    public function test_manager_can_remove_employee_from_assessment(): void
    {
        $this->actingAs($this->manager())->delete(self::BASE."/{$this->assessment->id}/TMON001")->assertRedirect();

        $this->assertDatabaseMissing('procedure_assignments', ['assessment_id' => $this->assessment->id, 'nrp' => 'TMON001']);
        $this->assertDatabaseMissing('procedure_evaluations', ['id' => $this->failedEvaluation->id]);
    }
}
