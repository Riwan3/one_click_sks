<?php

namespace Tests\Feature\OneSetting;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ManpowerV201Test extends TestCase
{
    use DatabaseTransactions;

    private const BASE = '/one-setting/manpower';

    private const DATE = '2026-10-05';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'tmp'.random_int(10000, 99999)]);
    }

    private function operator(): User
    {
        return User::factory()->create(['role' => 'operator', 'username' => 'tmpop'.random_int(10000, 99999)]);
    }

    private function createSetting(User $admin, string $date = self::DATE, string $shift = 'D'): int
    {
        $this->actingAs($admin)->post(self::BASE.'/settings', ['date' => $date, 'shift' => $shift])->assertSessionHas('success');

        return (int) DB::table('v2_mp_settings')->where(['work_date' => $date, 'shift' => $shift])->value('id');
    }

    public function test_every_page_renders_with_real_data(): void
    {
        $admin = $this->admin();
        $pages = [
            '' => 'Harian', '/preview' => 'Preview', '/master' => 'Master',
            '/monitoring' => 'Monitoring', '/monitoring-mp' => 'MonitoringMp',
        ];

        foreach ($pages as $path => $component) {
            $this->actingAs($admin)->get(self::BASE.$path.'?date=2026-10-01&shift=D&month=2026-10')
                ->assertOk()
                ->assertInertia(fn (AssertableInertia $page) => $page
                    ->component("OneSetting/ManpowerV201/$component")
                    ->where('ready', true)
                    ->where('loadError', null));
        }
    }

    public function test_harian_shows_existing_setting_tree_and_sisa(): void
    {
        $this->actingAs($this->admin())->get(self::BASE.'?date=2026-10-01&shift=D')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('view.setting.work_date', '2026-10-01')
                ->has('view.tree', 3)
                ->has('view.summary.units')
                ->has('view.sisa'));
    }

    public function test_monitoring_mp_includes_roster_and_ss6_codes(): void
    {
        $this->actingAs($this->admin())->get(self::BASE.'/monitoring-mp?date=2026-10-01&shift=D')
            ->assertInertia(fn (AssertableInertia $page) => $page->has('view.codes.roster')->has('view.codes.ss6'));
    }

    public function test_create_setting_pulls_roster_then_assign_unassign_and_lain(): void
    {
        $admin = $this->admin();
        $sid = $this->createSetting($admin);
        $this->assertGreaterThan(0, DB::table('v2_mp_assignments')->where('setting_id', $sid)->count());

        $this->actingAs($admin)->post(self::BASE.'/settings', ['date' => self::DATE, 'shift' => 'D'])
            ->assertSessionHasErrors('form');

        $assignment = DB::table('v2_mp_assignments')->where('setting_id', $sid)->first();
        $unit = self::BASE."/settings/$sid/units/{$assignment->unit_id}";
        $this->actingAs($admin)->delete("$unit/assign")->assertSessionHas('success');
        $this->assertDatabaseMissing('v2_mp_assignments', ['id' => $assignment->id]);
        $this->assertDatabaseHas('v2_mp_released', ['setting_id' => $sid, 'nrp' => $assignment->nrp]);

        $this->actingAs($admin)->post("$unit/assign", ['nrp' => $assignment->nrp])->assertSessionHas('success');
        $this->assertDatabaseHas('v2_mp_assignments', ['setting_id' => $sid, 'nrp' => $assignment->nrp, 'source' => 'manual']);

        $this->actingAs($admin)->delete("$unit/assign");
        $lain = self::BASE."/settings/$sid/lain";
        $this->actingAs($admin)->post($lain, ['nrp' => $assignment->nrp, 'label' => 'standby'])->assertSessionHas('success');
        $this->assertDatabaseHas('v2_mp_lain', ['setting_id' => $sid, 'nrp' => $assignment->nrp, 'label' => 'STANDBY']);
        $this->actingAs($admin)->delete($lain, ['nrp' => $assignment->nrp])->assertSessionHas('success');
        $this->assertDatabaseMissing('v2_mp_lain', ['setting_id' => $sid, 'nrp' => $assignment->nrp]);
    }

    public function test_final_setting_rejects_changes_until_reopened(): void
    {
        $admin = $this->admin();
        $sid = $this->createSetting($admin);

        $this->actingAs($admin)->post(self::BASE."/settings/$sid/finalize")->assertSessionHas('success');
        $this->actingAs($admin)->post(self::BASE."/settings/$sid/sync")->assertSessionHasErrors('form');

        $this->actingAs($admin)->post(self::BASE."/settings/$sid/reopen")->assertSessionHas('success');
        $this->actingAs($admin)->post(self::BASE."/settings/$sid/sync")->assertSessionHas('success');
    }

    public function test_candidates_endpoint_returns_json_and_validates_unit(): void
    {
        $admin = $this->admin();
        $unitId = (int) DB::table('v2_mp_units')->where('is_spare_slot', 0)->value('id');

        $this->actingAs($admin)->getJson(self::BASE."/candidates?date=2026-10-01&shift=D&unit_id=$unitId")
            ->assertOk()->assertJsonPath('success', true);
        $this->actingAs($admin)->getJson(self::BASE.'/candidates?date=2026-10-01&shift=D')
            ->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_master_crud_and_lock_rules(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(self::BASE.'/master/csa', ['name' => 'csa uji'])->assertSessionHas('success');
        $csa = DB::table('v2_mp_csa')->where('name', 'CSA UJI')->first();

        $this->actingAs($admin)->post(self::BASE.'/master/csa', ['name' => 'CSA UJI'])->assertSessionHasErrors('form');
        $this->actingAs($admin)->post(self::BASE.'/master/categories', ['csa_id' => $csa->id, 'name' => 'kat a', 'col_pos' => 1])
            ->assertSessionHas('success');
        $cat = DB::table('v2_mp_categories')->where('csa_id', $csa->id)->first();

        $this->actingAs($admin)->post(self::BASE.'/master/units', ['category_id' => $cat->id, 'units' => "ZZ9001, spare 9001\nzz9001", 'high_risk' => true])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('v2_mp_units', ['unit' => 'ZZ9001', 'is_high_risk' => 1]);
        $this->assertDatabaseHas('v2_mp_units', ['unit' => 'SPARE 9001', 'is_spare_slot' => 1, 'is_high_risk' => 0]);

        $this->actingAs($admin)->put(self::BASE."/master/csa/{$csa->id}/lock", ['locked' => true])->assertSessionHas('success');
        $this->actingAs($admin)->put(self::BASE."/master/csa/{$csa->id}", ['name' => 'BARU'])->assertSessionHasErrors('form');
        $this->actingAs($admin)->put(self::BASE."/master/csa/{$csa->id}/lock", ['locked' => false]);
        $this->actingAs($admin)->delete(self::BASE."/master/categories/{$cat->id}")->assertSessionHasErrors('form');
        $this->actingAs($admin)->delete(self::BASE."/master/csa/{$csa->id}")->assertSessionHas('success');
        $this->assertDatabaseMissing('v2_mp_csa', ['id' => $csa->id]);
    }

    public function test_user_without_permission_gets_403_everywhere(): void
    {
        $user = $this->operator();

        foreach (['', '/preview', '/master', '/monitoring', '/monitoring-mp', '/candidates'] as $path) {
            $this->actingAs($user)->get(self::BASE.$path)->assertForbidden();
        }
        $this->actingAs($user)->post(self::BASE.'/settings', ['date' => self::DATE, 'shift' => 'D'])->assertForbidden();
        $this->actingAs($user)->post(self::BASE.'/master/csa', ['name' => 'X'])->assertForbidden();
        $this->assertDatabaseMissing('v2_mp_settings', ['work_date' => self::DATE, 'shift' => 'D']);
    }
}
