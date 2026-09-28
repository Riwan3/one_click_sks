<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\Procedure;
use App\Models\ProcedureAssessment;
use App\Models\ProcedureCertificate;
use App\Models\ProcedureEvaluation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ProcedureCertificateTest extends TestCase
{
    use DatabaseTransactions;

    private const BASE = '/one-procedure/evaluasi/certificates';

    private ProcedureAssessment $assessment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);

        $procedure = Procedure::create(['title' => 'SOP Test Sertifikat', 'risk_level' => 'high']);
        $this->assessment = ProcedureAssessment::create([
            'procedure_id' => $procedure->id, 'title' => 'Assessment Test Sertifikat', 'status' => 'published', 'validity_months' => 6,
        ]);
        ProcedureEvaluation::create(['assessment_id' => $this->assessment->id, 'nrp' => 'TCERT01', 'score' => 90, 'status' => 'passed', 'completed_at' => now()]);
    }

    private function manager(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'tcert'.random_int(1000, 9999)]);
    }

    public function test_issue_all_creates_certificate_with_v1_code_format(): void
    {
        $this->actingAs($this->manager())->post(self::BASE.'/issue-all', ['assessment_id' => $this->assessment->id])
            ->assertSessionHas('success', '1 sertifikat diterbitkan');

        $certificate = ProcedureCertificate::where('assessment_id', $this->assessment->id)->firstOrFail();
        $this->assertMatchesRegularExpression('/^EP-\d{6}-[A-Z2-9]{6}$/', $certificate->certificate_code);
        $this->assertSame(now()->addMonths(6)->toDateString(), $certificate->valid_until->toDateString());

        $this->actingAs($this->manager())->post(self::BASE.'/issue-all', ['assessment_id' => $this->assessment->id])
            ->assertSessionHas('success', '0 sertifikat diterbitkan');
    }

    public function test_certificate_near_expiry_is_marked_expiring_soon(): void
    {
        ProcedureCertificate::create([
            'assessment_id' => $this->assessment->id, 'nrp' => 'TCERT01', 'certificate_code' => 'EP-TEST-EXPSN1',
            'score' => 90, 'issue_date' => now()->subMonths(6), 'valid_until' => now()->addDays(10), 'status' => 'active',
        ]);

        $this->actingAs($this->manager())
            ->get('/one-procedure/evaluasi?tab=certification&search=TCERT01')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('certification.rows.0.status', 'expiring_soon')
                ->where('certification.rows.0.days_left', 10));
    }

    public function test_revoke_and_public_verification(): void
    {
        $certificate = ProcedureCertificate::create([
            'assessment_id' => $this->assessment->id, 'nrp' => 'TCERT01', 'certificate_code' => 'EP-TEST-RVK001',
            'score' => 90, 'issue_date' => now(), 'valid_until' => now()->addYear(), 'status' => 'active',
        ]);

        $this->get('/sertifikat-prosedur/verify?code=EP-TEST-RVK001')->assertOk()->assertSee('Sertifikat Valid');

        $this->actingAs($this->manager())->post(self::BASE."/{$certificate->id}/revoke")->assertSessionHas('success', 'Sertifikat dicabut');

        auth()->logout();
        $this->get('/sertifikat-prosedur/verify?code=EP-TEST-RVK001')->assertOk()->assertSee('Sertifikat Dicabut');
    }

    public function test_pdf_page_renders_certificate(): void
    {
        $certificate = ProcedureCertificate::create([
            'assessment_id' => $this->assessment->id, 'nrp' => 'TCERT01', 'certificate_code' => 'EP-TEST-PDF001',
            'score' => 90, 'issue_date' => now(), 'valid_until' => ProcedureCertificate::LIFETIME_DATE, 'status' => 'active',
        ]);

        $this->actingAs($this->manager())->get(self::BASE."/{$certificate->certificate_code}/pdf")
            ->assertOk()->assertSee('SERTIFIKAT KOMPETENSI')->assertSee('Berlaku Selamanya');
    }

    public function test_operator_cannot_issue_or_revoke(): void
    {
        $operator = User::factory()->create(['role' => 'operator', 'username' => 'tcertop'.random_int(1000, 9999)]);

        $this->actingAs($operator)->post(self::BASE.'/issue-all')->assertForbidden();
    }
}
