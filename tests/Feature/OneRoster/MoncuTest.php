<?php

namespace Tests\Feature\OneRoster;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class MoncuTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'tmoncu'.random_int(1000, 9999)]);
    }

    private function operator(): User
    {
        return User::factory()->create(['role' => 'operator', 'username' => 'tmoncuop'.random_int(1000, 9999)]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
    }

    public function test_admin_sees_moncu_overview_with_periods(): void
    {
        $this->actingAs($this->admin())
            ->get('/one-roster/moncu?year='.date('Y'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('OneRoster/Moncu/Index')
                ->where('canManage', true)
                ->has('rows.0.periods', 5)
                ->has('pagination.pages'));
    }

    public function test_non_authorized_user_gets_403_on_all_moncu_pages(): void
    {
        $user = $this->operator();

        foreach (['moncu', 'moncu-monitoring', 'moncu-calendar'] as $page) {
            $this->actingAs($user)->get("/one-roster/{$page}")->assertForbidden();
        }
        $this->actingAs($user)->post('/one-roster/moncu/periods', ['nrp' => '1', 'year' => 2026, 'period_no' => 1])->assertForbidden();
    }

    public function test_monitoring_summarizes_status_and_serves_detail(): void
    {
        $user = $this->admin();
        $nrp = (string) DB::table('v2_moncu_employees')->where('is_active', 1)->value('nrp');

        $this->actingAs($user)->get('/one-roster/moncu-monitoring?sort=nama&dir=desc')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('OneRoster/MoncuMonitoring/Index')
                ->has('summary.total')
                ->has('rows.0.status'));

        $this->actingAs($user)->getJson("/one-roster/moncu-monitoring/detail?nrp={$nrp}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.employee.nrp', $nrp)
            ->assertJsonCount(5, 'data.periods');

        $this->actingAs($user)->getJson('/one-roster/moncu-monitoring/detail?nrp=TIDAKADA')
            ->assertStatus(404)->assertJsonPath('success', false);
    }

    public function test_calendar_builds_month_grid_and_day_detail(): void
    {
        $user = $this->admin();
        $awal = DB::table('v2_moncu_periods')->whereNotNull('awal')->orderByDesc('awal')->value('awal');
        [$year, $month] = [substr($awal, 0, 4), (int) substr($awal, 5, 2)];

        $this->actingAs($user)->get("/one-roster/moncu-calendar?year={$year}&month={$month}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('OneRoster/MoncuCalendar/Index')
                ->where('hasParamError', false)
                ->where('summary.ct_employee_day', fn ($n) => $n > 0));

        $this->actingAs($user)->get('/one-roster/moncu-calendar?year=1999')
            ->assertOk()->assertInertia(fn (AssertableInertia $page) => $page->where('hasParamError', true));

        $this->actingAs($user)->getJson("/one-roster/moncu-calendar/day?date={$awal}")
            ->assertOk()->assertJsonPath('data.date', $awal)->assertJsonStructure(['data' => ['ct', 'id', 'plan']]);

        $this->actingAs($user)->getJson('/one-roster/moncu-calendar/day?date=2026-02-31')->assertStatus(422);
    }

    public function test_preview_period_calculates_end_induction_and_plan(): void
    {
        $nrp = (string) DB::table('v2_moncu_employees')->where('is_active', 1)->value('nrp');

        $this->actingAs($this->admin())
            ->getJson("/one-roster/moncu/periods/preview?nrp={$nrp}&year=2026&period_no=1&awal=2026-01-05&tahunan=2&izin_resmi=0")
            ->assertOk()
            ->assertJsonPath('data.akhir', '2026-01-20')
            ->assertJsonPath('data.induksi', '2026-01-21');
    }

    public function test_admin_can_save_and_delete_period_and_edit_employee(): void
    {
        $user = $this->admin();
        $emp = DB::table('v2_moncu_employees')->where('is_active', 1)->first();
        DB::table('v2_moncu_periods')->where('nrp', $emp->nrp)->where('cycle_year', 2031)->delete();

        $this->actingAs($user)->post('/one-roster/moncu/periods', [
            'nrp' => $emp->nrp, 'year' => 2031, 'period_no' => 1, 'awal' => '2031-01-06', 'tahunan' => '0', 'izin_resmi' => '0',
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('v2_moncu_periods', ['nrp' => $emp->nrp, 'cycle_year' => 2031, 'akhir' => '2031-01-19']);

        $this->actingAs($user)->post('/one-roster/moncu/periods', [
            'nrp' => $emp->nrp, 'year' => 2031, 'period_no' => 2, 'awal' => '2031-01-10', 'tahunan' => '0', 'izin_resmi' => '0',
        ])->assertSessionHasErrors('form');

        $this->actingAs($user)->delete('/one-roster/moncu/periods', ['nrp' => $emp->nrp, 'year' => 2031, 'period_no' => 1])
            ->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseMissing('v2_moncu_periods', ['nrp' => $emp->nrp, 'cycle_year' => 2031]);

        $this->actingAs($user)->post('/one-roster/moncu/employees', [
            'nrp' => $emp->nrp, 'hiring_date' => $emp->hiring_date, 'group_name' => $emp->group_name,
        ])->assertRedirect()->assertSessionHas('success');
        $this->actingAs($user)->post('/one-roster/moncu/employees', [
            'nrp' => 'NRPTIDAKADA', 'hiring_date' => '2020-01-01',
        ])->assertSessionHasErrors('form');
    }
    public function test_employee_search_and_sync_endpoints_work_for_admin_and_reject_operator(): void
    {
        $admin = $this->admin();
        $employee = DB::table('employees')->whereNotNull('name')->first();

        $this->actingAs($admin)->getJson('/one-roster/moncu/employees/search?q='.substr($employee->nrp, 0, 4))
            ->assertOk()->assertJsonPath('success', true);

        $rosterId = DB::table('v2_rosters')->where('status', '<>', 'archived')->value('id');
        $this->actingAs($admin)->postJson('/one-roster/moncu/sync', ['roster_id' => $rosterId])
            ->assertOk()->assertJsonStructure(['data' => ['created', 'changed', 'reverted', 'message', 'warnings']]);

        $this->actingAs($admin)->postJson('/one-roster/moncu/sync', ['roster_id' => 99999999])
            ->assertStatus(404)->assertJsonPath('success', false);

        $this->actingAs($this->operator())->postJson('/one-roster/moncu/sync', ['roster_id' => $rosterId])->assertForbidden();
    }

    public function test_sheet_import_preview_rejects_invalid_url_and_operator(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/one-roster/moncu/sheet-import/preview', ['url' => 'bukan-link'])
            ->assertStatus(422)->assertJsonPath('success', false);

        $this->actingAs($this->operator())
            ->postJson('/one-roster/moncu/sheet-import/preview', ['url' => 'https://docs.google.com/spreadsheets/d/x/edit?gid=1'])
            ->assertForbidden();
    }
}