<?php

namespace Tests\Feature;

use App\Models\AcademyAssessment;
use App\Models\AcademyMateri;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EvaluasiKompetensiControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->create(['username' => 'tek'.random_int(10000, 99999), 'role' => 'admin']);
    }

    private function employee(string $jabatanRoster): Employee
    {
        return Employee::create([
            'nrp' => 'EK'.random_int(10000, 99999),
            'name' => 'Peserta Kompetensi',
            'jabatan_ss6' => $jabatanRoster,
            'jabatan_roster' => $jabatanRoster,
            'unit' => 'Produksi',
            'lokasi_kerja' => 'Site A',
            'status_karyawan' => 'FIT',
        ]);
    }

    public function test_full_admin_lifecycle_materi_assessment_monitoring_raport(): void
    {
        $admin = $this->admin();
        $employee = $this->employee('ZZZ_TEST_ROLE_UNIQUE_'.random_int(1000, 9999));

        // 1. Create materi (with file upload)
        $file = UploadedFile::fake()->create('k3-dasar.pdf', 500, 'application/pdf');
        $this->actingAs($admin)
            ->post('/one-academy/evaluasi-kompetensi/materi', [
                'title' => 'K3 Dasar', 'category' => 'K3', 'risk_level' => 'high', 'file' => $file,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $materi = AcademyMateri::where('title', 'K3 Dasar')->firstOrFail();
        $this->assertNotNull($materi->file_path);

        // 2. Update materi
        $this->actingAs($admin)
            ->post("/one-academy/evaluasi-kompetensi/materi/{$materi->id}", ['title' => 'K3 Dasar Revisi', 'risk_level' => 'critical'])
            ->assertSessionHasNoErrors();
        $this->assertSame('K3 Dasar Revisi', $materi->fresh()->title);

        // 3. Materi tab lists it with correct counts
        $this->actingAs($admin)
            ->get('/one-academy/evaluasi-kompetensi?tab=materi')
            ->assertInertia(fn (Assert $page) => $page
                ->component('OneAcademy/EvaluasiKompetensi/Index')
                ->where('activeTab', 'materi')
                ->has('materiList', 1)
            );

        // 4. Create assessment targeting the employee's jabatan_roster
        $this->actingAs($admin)
            ->post('/one-academy/evaluasi-kompetensi/assessment', [
                'procedure_id' => $materi->id, 'title' => 'Ujian K3 Dasar', 'passing_grade' => 70,
                'target_roles' => [$employee->jabatan_roster],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $assessment = AcademyAssessment::where('title', 'Ujian K3 Dasar')->firstOrFail();
        $this->assertSame('draft', $assessment->status);

        // 5. Add a multiple_choice question
        $this->actingAs($admin)
            ->post("/one-academy/evaluasi-kompetensi/assessment/{$assessment->id}/questions", [
                'question_type' => 'multiple_choice', 'question' => 'Apa itu APD?',
                'option_a' => 'Alat Pelindung Diri', 'option_b' => 'Lainnya', 'correct' => 'A',
            ])
            ->assertSessionHasNoErrors();
        $this->assertSame(1, $assessment->questions()->count());

        // 6. Publish -> should assign the matching employee
        $this->actingAs($admin)
            ->post("/one-academy/evaluasi-kompetensi/assessment/{$assessment->id}/publish")
            ->assertSessionHasNoErrors()
            ->assertRedirect();
        $assessment->refresh();
        $this->assertSame('published', $assessment->status);
        $this->assertDatabaseHas('academy_notifications', ['assessment_id' => $assessment->id, 'nrp' => $employee->nrp]);

        // 7. Assessment manage view exposes stats
        $this->actingAs($admin)
            ->get("/one-academy/evaluasi-kompetensi?tab=assessment&manage={$assessment->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->where('assessment.manage.id', $assessment->id)
                ->where('assessment.stats.total_questions', 1)
                ->has('assessment.questions', 1)
            );

        // 8. Record a manual result for the employee
        $this->actingAs($admin)
            ->post('/one-academy/evaluasi-kompetensi/record-result', [
                'assessment_id' => $assessment->id, 'nrp' => $employee->nrp, 'score' => 88,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();
        $this->assertDatabaseHas('academy_results', ['assessment_id' => $assessment->id, 'nrp' => $employee->nrp, 'passed' => 1]);

        // 9. Monitoring tab reflects the completed/passed result
        $this->actingAs($admin)
            ->get('/one-academy/evaluasi-kompetensi?tab=monitoring')
            ->assertInertia(fn (Assert $page) => $page
                ->where('monitoring.summary.total_assigned', 1)
                ->where('monitoring.summary.completed', 1)
                ->where('monitoring.summary.passed', 1)
            );

        // 10. Monitoring detail endpoint (JSON) resolves the result with answers/history
        $resultId = DB::table('academy_results')->where('assessment_id', $assessment->id)->value('id');
        $this->actingAs($admin)
            ->get("/one-academy/evaluasi-kompetensi/monitoring/{$resultId}")
            ->assertOk()
            ->assertJsonPath('result.passed', true);

        // 11. Raport tab shows the employee with 1/1 passed
        $this->actingAs($admin)
            ->get('/one-academy/evaluasi-kompetensi?tab=raport')
            ->assertInertia(fn (Assert $page) => $page
                ->where('raport.rows.0.total_assigned', 1)
                ->where('raport.rows.0.total_passed', 1)
            );

        // 12. Raport detail endpoint (JSON)
        $this->actingAs($admin)
            ->get("/one-academy/evaluasi-kompetensi/raport/{$employee->nrp}")
            ->assertOk()
            ->assertJsonPath('total_passed', 1);

        // 13. Archive assessment
        $this->actingAs($admin)
            ->post("/one-academy/evaluasi-kompetensi/assessment/{$assessment->id}/archive")
            ->assertSessionHasNoErrors();
        $this->assertSame('archived', $assessment->fresh()->status);

        // 14. Delete assessment then materi
        $this->actingAs($admin)->delete("/one-academy/evaluasi-kompetensi/assessment/{$assessment->id}")->assertSessionHasNoErrors();
        $this->assertNull(AcademyAssessment::find($assessment->id));

        $this->actingAs($admin)->delete("/one-academy/evaluasi-kompetensi/materi/{$materi->id}")->assertSessionHasNoErrors();
        $this->assertNull(AcademyMateri::find($materi->id));
    }
}
