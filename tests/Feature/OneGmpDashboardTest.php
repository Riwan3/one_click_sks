<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OneGmpDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function baseUser(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'admin'.random_int(10000, 99999)]);
    }

    private function seedForm(string $lokasi, string $week, array $scores): void
    {
        $formId = DB::table('gmp_penilaian_form')->insertGetId([
            'tipe_gmp' => 'MHR',
            'week' => $week,
            'lokasi' => $lokasi,
            'penilai' => 'Tester',
            'nrp' => '999999',
            'tanggal_gmp' => '2026-09-01',
            'total_score' => array_sum($scores),
            'total_percent' => 50,
            'question_count' => count($scores),
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($scores as $qNo => $raw) {
            DB::table('gmp_penilaian_form_detail')->insert([
                'form_id' => $formId,
                'question_no' => $qNo,
                'section_key' => 'teknis',
                'criterion_title' => 'Kriteria '.$qNo,
                'criterion_name' => 'Kriteria '.$qNo,
                'score_raw' => $raw,
                'score_percent' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function test_area_renders_pareto_rekap_and_log_for_selected_week(): void
    {
        $user = $this->baseUser();
        $week = 'Week 36 - 2026';

        $this->seedForm('Lokasi A', $week, [1 => 0, 2 => 5, 3 => 5]);
        $this->seedForm('Lokasi B', $week, [1 => 5, 2 => 1, 3 => 5]);

        $response = $this->actingAs($user)->get('/one-gmp/area?type=MHR&year=2026&week='.urlencode($week));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('OneGmp/AreaIndex')
            ->where('filters.type', 'MHR')
            ->where('filters.week', $week)
            ->where('totalLocations', 2)
            ->has('pareto', 12)
            ->has('rekapWeighted', 2)
            ->has('rekapRaw', 2)
            ->has('detailLog', 2)
        );

        // Kriteria 1 (Q1): Lokasi A skor raw 0 (0%), Lokasi B skor raw 5 (100%) -> rata-rata performa = 50%.
        $pareto = collect($response->viewData('page')['props']['pareto']);
        $q1 = $pareto->firstWhere('no', 1);
        $this->assertSame(50.0, $q1['value']);

        // Rekap raw diurutkan dari total terendah -> lokasi A (0+5+5=10) harus di baris pertama.
        $rekapRaw = $response->viewData('page')['props']['rekapRaw'];
        $this->assertSame('Lokasi A', $rekapRaw[0]['lokasi']);
    }

    public function test_area_handles_no_data_gracefully(): void
    {
        $user = $this->baseUser();

        $response = $this->actingAs($user)->get('/one-gmp/area?type=MFCO&year=2026');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('OneGmp/AreaIndex')
            ->where('totalLocations', 0)
            ->where('filters.week', '')
        );
    }

    public function test_dashboard_renders_overview(): void
    {
        $user = $this->baseUser();

        $response = $this->actingAs($user)->get('/one-gmp/dashboard?year=2026');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('OneGmp/Dashboard')
            ->has('pareto')
            ->has('types')
        );
    }
}
