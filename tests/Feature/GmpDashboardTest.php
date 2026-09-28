<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class GmpDashboardTest extends TestCase
{
    use DatabaseTransactions;

    private const YEAR = 2031;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'username' => 'tgmp'.substr(md5($role), 0, 5).random_int(1000, 9999)]);
    }

    /** @param array<int,int> $scores [question_no => score_raw] */
    private function seedForm(string $lokasi, int $week, array $scores, string $status = 'approved'): void
    {
        $formId = DB::table('gmp_penilaian_form')->insertGetId([
            'tipe_gmp' => 'MFOB', 'week' => sprintf('Week %02d - %d', $week, self::YEAR), 'lokasi' => $lokasi,
            'penilai' => 'Tester', 'nrp' => '999999', 'tanggal_gmp' => self::YEAR.'-03-0'.($week % 9 + 1),
            'total_score' => array_sum($scores), 'total_percent' => 0, 'question_count' => count($scores),
            'status' => $status, 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ($scores as $no => $raw) {
            DB::table('gmp_penilaian_form_detail')->insert([
                'form_id' => $formId, 'question_no' => $no, 'section_key' => 'teknis', 'criterion_title' => "Q{$no}",
                'criterion_name' => "Q{$no}", 'score_raw' => $raw, 'score_percent' => 0, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function test_dashboard_uses_latest_week_and_weighted_area_scores(): void
    {
        // MFOB bobot Q1=14, Q2=12 (config/gmp_scoring.php). Lokasi A: 100%*14 + 100%*12 = 26; Lokasi B: 0 + 50%*12 = 6
        $this->seedForm('Lokasi A', 10, [1 => 5, 2 => 5]);
        $this->seedForm('Lokasi B', 10, [1 => 0, 2 => 1]);
        $this->seedForm('Lokasi A', 9, [1 => 0, 2 => 0]);
        $this->seedForm('Lokasi C', 10, [1 => 5, 2 => 5], 'rejected');

        $this->actingAs($this->user('group-leader'))
            ->get('/one-gmp/dashboard?gmp_type=MFOB&tahun='.self::YEAR)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('OneGmp/Dashboard')
                ->where('filters.week', 10)
                ->where('weekOptions', [10, 9])
                ->where('stats.total_penilaian', 2)
                ->where('stats.max_score', 26)
                ->where('stats.min_score', 6)
                ->where('chartWeek', 'Week 10 - '.self::YEAR)
                ->where('pareto.0.value', 50)
                ->has('locationCharts', 2)
                ->where('lokasi.0.lokasi', 'Lokasi A'));
    }

    public function test_all_weeks_aggregates_year_and_falls_back_to_latest_week_for_charts(): void
    {
        $this->seedForm('Lokasi A', 10, [1 => 5, 2 => 5]);
        $this->seedForm('Lokasi A', 9, [1 => 0, 2 => 0]);

        $this->actingAs($this->user('admin'))
            ->get('/one-gmp/dashboard?gmp_type=MFOB&week=&tahun='.self::YEAR)
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('filters.week', '')
                ->where('stats.total_penilaian', 2)
                ->where('stats.avg_score', 13)
                ->where('lokasi.0.avg_score', 13)
                ->where('chartWeek', 'Week 10 - '.self::YEAR));
    }

    public function test_other_gmp_pages_still_render_after_controller_restore(): void
    {
        $admin = $this->user('admin');

        foreach (['/one-gmp/area?type=MHR', '/one-gmp/trend', '/one-gmp/penilaian/MHR', '/one-gmp/approval', '/one-gmp/master'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_trend_page_builds_per_type_and_full_series(): void
    {
        foreach ([['MFOB', 'Week 09', 80], ['MFOB', 'Week 10', 90], ['MHR', 'Week 10', 70]] as [$type, $week, $pct]) {
            DB::table('gmp_penilaian_form')->insert([
                'tipe_gmp' => $type, 'week' => "{$week} - ".self::YEAR, 'lokasi' => 'L', 'penilai' => 'T', 'nrp' => '1',
                'tanggal_gmp' => self::YEAR.'-03-05', 'total_score' => 0, 'total_percent' => $pct, 'question_count' => 1,
                'status' => 'approved', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $this->actingAs($this->user('admin'))->get('/one-gmp/trend?year='.self::YEAR)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('trend.weeks', ['Week 09 - '.self::YEAR, 'Week 10 - '.self::YEAR])
                ->where('trend.series.MFOB.Week 10 - '.self::YEAR, 90)
                ->where('trend.series.FULL.Week 10 - '.self::YEAR, 80)
                ->where('trend.series.FULL.Week 09 - '.self::YEAR, 80));
    }

    public function test_operator_does_not_see_charts(): void
    {
        $this->actingAs($this->user('operator'))->get('/one-gmp/dashboard')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('canViewCharts', false));
    }
}
