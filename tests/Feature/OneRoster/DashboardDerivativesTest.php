<?php

namespace Tests\Feature\OneRoster;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DashboardDerivativesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'tdash'.random_int(1000, 9999)]);
    }

    private function operator(): User
    {
        return User::factory()->create(['role' => 'operator', 'username' => 'tdashop'.random_int(1000, 9999)]);
    }

    public function test_admin_sees_dashboard_summary_for_published_date(): void
    {
        $date = (string) DB::table('v2_rosters')->where('status', 'published')->value('period_start');

        $this->actingAs($this->admin())
            ->get('/one-roster/dashboard?date='.$date)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('OneRoster/Dashboard/Index')
                ->where('status', 'ok')
                ->where('filters.date', $date)
                ->has('dash.total.orang')
                ->has('dash.jabatan_list'));
    }

    public function test_non_authorized_user_gets_403_on_view_only_pages(): void
    {
        $user = $this->operator();

        $this->actingAs($user)->get('/one-roster/dashboard')->assertForbidden();
        $this->actingAs($user)->get('/one-roster/analisis-harian')->assertForbidden();
        $this->actingAs($user)->getJson('/one-roster/analisis-harian/data')->assertForbidden();
        $this->actingAs($user)->post('/one-roster/analisis-harian/target', ['roster_id' => 1, 'section_id' => 1])->assertForbidden();
    }

    public function test_analysis_page_and_data_endpoint_return_same_structure(): void
    {
        $user = $this->admin();
        $query = '?start='.date('Y-m-d').'&days=14';

        $this->actingAs($user)->get('/one-roster/analisis-harian'.$query)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('OneRoster/Analisis/Index')
                ->where('status', 'ok')
                ->where('filters.days', 14)
                ->has('analysis.dates', 14)
                ->where('canManage', true));

        $this->actingAs($user)->getJson('/one-roster/analisis-harian/data'.$query)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(14, 'data.dates');
    }

    public function test_analysis_target_can_be_saved_and_rejects_invalid_value(): void
    {
        $section = DB::table('v2_roster_sections')->first();
        if (! $section) {
            $this->markTestSkipped('Belum ada section roster pada database uji.');
        }
        $payload = ['roster_id' => $section->roster_id, 'section_id' => $section->id];

        $this->actingAs($this->admin())
            ->from('/one-roster/analisis-harian')
            ->post('/one-roster/analisis-harian/target', $payload + ['target_siang' => 5, 'target_malam' => ''])
            ->assertRedirect('/one-roster/analisis-harian')
            ->assertSessionHas('success');

        $this->actingAs($this->admin())
            ->from('/one-roster/analisis-harian')
            ->post('/one-roster/analisis-harian/target', $payload + ['target_siang' => 'abc'])
            ->assertSessionHasErrors('target');
    }

    public function test_monitoring_departemen_builds_matrix_for_published_month(): void
    {
        $period = DB::table('v2_rosters')->where('status', 'published')->first();
        $year = (int) substr($period->period_start, 0, 4);
        $month = (int) substr($period->period_start, 5, 2);

        $this->actingAs($this->admin())
            ->get("/one-roster/monitoring-departemen?tahun={$year}&bulan={$month}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('OneRoster/Produksi/Index')
                ->where('filters.bulan', $month)
                ->has('monitoring.days')
                ->has('monitoring.rows.0.cells')
                ->has('monitoring.daily', 10)
                ->has('monitoring.grandTotal.total'));
    }

    public function test_monitoring_departemen_is_open_to_any_logged_in_user(): void
    {
        $this->actingAs($this->operator())->get('/one-roster/monitoring-departemen')->assertOk();
    }

    public function test_share_roster_admin_picks_employee_and_regular_user_sees_own_only(): void
    {
        $cell = DB::table('v2_roster_details as d')
            ->join('v2_rosters as r', 'r.id', '=', 'd.roster_id')
            ->where('r.status', 'published')
            ->select('d.nrp', 'd.work_date')
            ->first();
        $year = (int) substr($cell->work_date, 0, 4);
        $month = (int) substr($cell->work_date, 5, 2);

        $this->actingAs($this->admin())
            ->get("/one-roster/share?nrp={$cell->nrp}&tahun={$year}&bulan={$month}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('OneRoster/Share/Index')
                ->where('share.canPick', true)
                ->where('share.nrp', (string) $cell->nrp)
                ->where('share.state', 'ok')
                ->has('share.cells'));

        $this->actingAs($this->operator())
            ->get("/one-roster/share?nrp={$cell->nrp}&tahun={$year}&bulan={$month}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('share.canPick', false)
                ->where('share.employees', [])
                ->where('share.nrp', fn ($nrp) => $nrp !== (string) $cell->nrp));
    }
}
