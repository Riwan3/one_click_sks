<?php

namespace Tests\Feature\OneSetting;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\FleetAllocation;
use App\Models\FleetMasterUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class FleetSettingTest extends TestCase
{
    use DatabaseTransactions;

    private const BASE = '/one-setting/fleet';

    private const DATE = '2031-03-05';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'tmp'.random_int(10000, 99999)]);
    }

    private function day(array $extra = []): array
    {
        return ['date' => self::DATE, 'shift' => 'D'] + $extra;
    }

    private function master(string $code, string $type, array $extra = []): FleetMasterUnit
    {
        return FleetMasterUnit::create(array_merge(['code' => $code, 'unit_type' => $type, 'is_active' => true], $extra));
    }

    private function rows(string $shift = 'D')
    {
        return FleetAllocation::where('tanggal', self::DATE)->where('shift', $shift === 'D' ? 1 : 2);
    }

    private function seedLoader(User $admin, string $loader = 'ZT-E1', string $group = 'ZT GROUP'): void
    {
        $this->master($loader, 'loader');
        $this->actingAs($admin)->post(self::BASE.'/settings/cluster', $this->day(['cluster_name' => $group]));
        $this->actingAs($admin)->post(self::BASE.'/settings/loader', $this->day(['loader_code' => $loader, 'cluster_name' => $group]))->assertSessionHas('success');
    }

    public function test_pages_render_with_real_data(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(self::BASE.'?date=2026-09-28&shift=D')->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('OneSetting/Fleet/Index')->where('ready', true)->where('loadError', null)
            ->has('view.groups')->where('menu', fn ($menu) => collect($menu)->contains('label', 'Setting Fleet')));

        $this->actingAs($admin)->get(self::BASE.'/master')->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('OneSetting/Fleet/Master')->where('ready', true)->where('loadError', null)
            ->has('loaders')->has('haulers')->has('pullPreview'));
    }

    public function test_pull_preview_maps_categories_and_pull_adds_without_duplicates(): void
    {
        $admin = $this->admin();
        $preview = collect($this->actingAs($admin)->get(self::BASE.'/master')->viewData('page')['props']['pullPreview']);

        $this->assertSame('loader', $preview->firstWhere('name', 'EXCAVATOR - 2000')['type']);
        $this->assertSame('hauler', $preview->firstWhere('name', 'DUMP TRUCK')['type']);
        $this->assertSame('skip', $preview->firstWhere('name', 'BULLDOZER - 155')['type']);

        $payload = ['categories' => $preview->map(fn ($c) => ['csa' => $c['csa'], 'name' => $c['name'], 'type' => $c['type']])->all()];
        $this->actingAs($admin)->post(self::BASE.'/master/pull', $payload)->assertSessionHas('success');
        $added = FleetMasterUnit::count();
        $this->assertGreaterThan(0, $added);
        $this->assertSame(0, FleetMasterUnit::whereNotIn('unit_type', ['loader', 'hauler'])->count());
        $this->assertSame(0, FleetMasterUnit::where('unit_type', 'loader')->whereNotNull('parking_code')->count());
        $this->assertSame('J', FleetMasterUnit::where('category', 'like', '%JUMBO%')->value('parking_code'));
        $this->assertSame('G', FleetMasterUnit::where('category', 'HEAVY DUMP TRUCK - KOMATSU 785')->where('parking_code', 'G')->value('parking_code'));
        $this->assertSame('M', FleetMasterUnit::where('category', 'OFF HIGHWAY TRUCK 777 - CATERPILLAR')->value('parking_code'));
        FleetMasterUnit::where('unit_type', 'hauler')->limit(3)->update(['parking_code' => null]);

        $this->actingAs($admin)->post(self::BASE.'/master/pull', $payload)->assertSessionHas('success');
        $this->assertSame($added, FleetMasterUnit::count());
        $this->assertSame(0, FleetMasterUnit::where('unit_type', 'hauler')->whereNull('parking_code')->count());
    }

    public function test_master_unit_crud_and_duplicate_code_rejected(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(self::BASE.'/master/units', ['code' => 'zt-h1', 'unit_type' => 'hauler'])->assertSessionHas('success');
        $unit = FleetMasterUnit::where('code', 'ZT-H1')->firstOrFail();

        $this->actingAs($admin)->post(self::BASE.'/master/units', ['code' => 'ZT-H1', 'unit_type' => 'hauler'])->assertSessionHasErrors('form');
        $this->actingAs($admin)->put(self::BASE."/master/units/{$unit->id}", ['category' => 'DUMP TRUCK'])->assertSessionHas('success');
        $this->actingAs($admin)->put(self::BASE."/master/units/{$unit->id}/active", ['is_active' => false])->assertSessionHas('success');
        $this->assertFalse($unit->fresh()->is_active);
        $this->assertSame('DUMP TRUCK', $unit->fresh()->category);

        $this->actingAs($admin)->delete(self::BASE."/master/units/{$unit->id}")->assertSessionHas('success');
        $this->assertDatabaseMissing('fleet_master_units', ['code' => 'ZT-H1']);
    }

    public function test_add_loader_blank_then_assign_copies_parking_move_and_release_hauler(): void
    {
        $admin = $this->admin();
        $this->seedLoader($admin, 'ZT-E1');
        $this->seedLoader($admin, 'ZT-E2');
        $this->master('ZT-H1', 'hauler', ['parking_code' => 'G']);

        $loader = $this->rows()->where('loader_id', 'ZT-E1')->first();
        $this->assertSame(['ZT GROUP', null, 0], [$loader->group_name, $loader->area, $loader->distance]);
        $this->actingAs($admin)->put(self::BASE.'/settings/loader', $this->day(['loader_code' => 'ZT-E1', 'area' => 'PIT A']))->assertSessionHas('success');

        $this->actingAs($admin)->post(self::BASE.'/settings/hauler', $this->day(['loader_code' => 'ZT-E1', 'hauler_code' => 'ZT-H1']))->assertSessionHas('success');
        $this->assertSame('G', $this->rows()->where('unit_nrp', 'ZT-H1')->value('unit_status'));
        $this->assertNull($this->rows()->where('loader_id', 'ZT-E1')->whereNull('unit_nrp')->first());

        $this->actingAs($admin)->post(self::BASE.'/settings/hauler/move', $this->day(['hauler_code' => 'ZT-H1', 'loader_code' => 'ZT-E2']))->assertSessionHas('success');
        $this->assertSame('ZT-E2', $this->rows()->where('unit_nrp', 'ZT-H1')->value('loader_id'));
        $this->assertNotNull($this->rows()->where('loader_id', 'ZT-E1')->whereNull('unit_nrp')->first());

        $this->actingAs($admin)->delete(self::BASE.'/settings/hauler', $this->day(['hauler_code' => 'ZT-H1']))->assertSessionHas('success');
        $this->assertSame(0, $this->rows()->whereNotNull('unit_nrp')->count());
        $this->assertSame(2, $this->rows()->count());
    }

    public function test_hauler_cannot_be_installed_on_two_loaders(): void
    {
        $admin = $this->admin();
        $this->seedLoader($admin, 'ZT-E1');
        $this->seedLoader($admin, 'ZT-E2');
        $this->master('ZT-H1', 'hauler');
        $this->actingAs($admin)->post(self::BASE.'/settings/hauler', $this->day(['loader_code' => 'ZT-E1', 'hauler_code' => 'ZT-H1']));

        $this->actingAs($admin)->post(self::BASE.'/settings/hauler', $this->day(['loader_code' => 'ZT-E2', 'hauler_code' => 'ZT-H1']))->assertSessionHasErrors('form');
        $this->assertSame(1, $this->rows()->where('unit_nrp', 'ZT-H1')->count());
    }

    public function test_hauler_must_exist_in_active_master(): void
    {
        $admin = $this->admin();
        $this->seedLoader($admin);
        $this->master('ZT-H9', 'hauler', ['is_active' => false]);

        $this->actingAs($admin)->post(self::BASE.'/settings/hauler', $this->day(['loader_code' => 'ZT-E1', 'hauler_code' => 'ZT-H9']))->assertSessionHasErrors('form');
        $this->actingAs($admin)->post(self::BASE.'/settings/loader', $this->day(['loader_code' => 'ZT-E1', 'cluster_name' => 'ZT GROUP']))->assertSessionHasErrors('form');
    }

    public function test_update_remove_loader_copy_and_clear(): void
    {
        $admin = $this->admin();
        $this->seedLoader($admin);
        $this->master('ZT-H1', 'hauler');
        $this->actingAs($admin)->post(self::BASE.'/settings/hauler', $this->day(['loader_code' => 'ZT-E1', 'hauler_code' => 'ZT-H1']));
        $this->actingAs($admin)->put(self::BASE.'/settings/loader', $this->day(['loader_code' => 'ZT-E1', 'area' => 'PIT B', 'distance' => 1200]))->assertSessionHas('success');
        $this->assertSame(1, $this->rows()->where(['area' => 'PIT B', 'distance' => 1200])->count());

        $target = ['date' => self::DATE, 'shift' => 'N'];
        $copy = ['from_date' => self::DATE, 'from_shift' => 'D'];
        $this->actingAs($admin)->post(self::BASE.'/settings/copy', $target + $copy)->assertSessionHas('success');
        $this->assertSame(1, $this->rows('N')->where('unit_nrp', 'ZT-H1')->count());
        $this->actingAs($admin)->post(self::BASE.'/settings/copy', $target + $copy)->assertSessionHasErrors('form');
        $this->actingAs($admin)->post(self::BASE.'/settings/copy', $target + $copy + ['replace' => true])->assertSessionHas('success');
        $this->assertSame(1, $this->rows('N')->count());

        $this->actingAs($admin)->delete(self::BASE.'/settings/clear', $target)->assertSessionHas('success');
        $this->assertSame(0, $this->rows('N')->count());
        $this->actingAs($admin)->delete(self::BASE.'/settings/loader', $this->day(['loader_code' => 'ZT-E1']))->assertSessionHas('success');
        $this->assertSame(0, $this->rows()->count());
    }

    public function test_view_summary_and_missing_operator_warning(): void
    {
        $admin = $this->admin();
        $this->seedLoader($admin);
        $this->master('ZT-H1', 'hauler');
        $this->master('ZT-H2', 'hauler');
        $this->actingAs($admin)->post(self::BASE.'/settings/hauler', $this->day(['loader_code' => 'ZT-E1', 'hauler_code' => 'ZT-H1']));

        $this->actingAs($admin)->get(self::BASE.'?date='.self::DATE.'&shift=D')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('view.summary.loaders', 1)->where('view.summary.haulers', 1)->has('view.warnings', 1)
            ->where('view.summary.sisa', fn ($sisa) => $sisa >= 1));
    }

    public function test_non_manager_gets_403_on_pages_and_writes(): void
    {
        $operator = User::factory()->create(['role' => 'operator', 'username' => 'tmpop'.random_int(10000, 99999)]);

        $this->actingAs($operator)->get(self::BASE)->assertForbidden();
        $this->actingAs($operator)->get(self::BASE.'/master')->assertForbidden();
        $this->actingAs($operator)->post(self::BASE.'/settings/loader', $this->day(['loader_code' => 'X']))->assertForbidden();
        $this->actingAs($operator)->post(self::BASE.'/master/units', ['code' => 'X', 'unit_type' => 'hauler'])->assertForbidden();
    }

    public function test_hauler_without_parking_is_installed_with_null_status_and_parking_is_validated(): void
    {
        $admin = $this->admin();
        $this->seedLoader($admin);
        $this->master('ZT-H1', 'hauler');
        $this->actingAs($admin)->post(self::BASE.'/settings/hauler', $this->day(['loader_code' => 'ZT-E1', 'hauler_code' => 'ZT-H1']))->assertSessionHas('success');
        $this->assertNull($this->rows()->where('unit_nrp', 'ZT-H1')->value('unit_status'));

        $this->actingAs($admin)->post(self::BASE.'/master/units', ['code' => 'ZT-H2', 'unit_type' => 'hauler', 'parking_code' => 'x'])->assertSessionHasErrors('form');
        $this->actingAs($admin)->post(self::BASE.'/master/units', ['code' => 'ZT-H2', 'unit_type' => 'hauler', 'parking_code' => 'j'])->assertSessionHas('success');
        $this->actingAs($admin)->post(self::BASE.'/master/units', ['code' => 'ZT-E9', 'unit_type' => 'loader', 'parking_code' => 'M'])->assertSessionHas('success');
        $this->assertSame('J', FleetMasterUnit::where('code', 'ZT-H2')->value('parking_code'));
        $this->assertNull(FleetMasterUnit::where('code', 'ZT-E9')->value('parking_code'));
    }

    private function viewProps(User $admin, string $shift = 'D'): array
    {
        return $this->actingAs($admin)->get(self::BASE.'?date='.self::DATE.'&shift='.$shift)->viewData('page')['props']['view'];
    }

    private function breakdownRows(string $shift = 'D')
    {
        return $this->rows($shift)->where('group_name', 'BREAKDOWN / SERVICE');
    }

    public function test_ready_lists_exclude_installed_and_breakdown_units_and_keep_parking(): void
    {
        $admin = $this->admin();
        $this->seedLoader($admin);
        foreach (['M' => 'ZT-HM', 'G' => 'ZT-HG', 'J' => 'ZT-HJ'] as $parking => $code) {
            $this->master($code, 'hauler', ['parking_code' => $parking]);
        }
        $this->master('ZT-HM2', 'hauler', ['parking_code' => 'M']);
        $this->actingAs($admin)->post(self::BASE.'/settings/hauler', $this->day(['loader_code' => 'ZT-E1', 'hauler_code' => 'ZT-HM']));
        $this->actingAs($admin)->post(self::BASE.'/settings/breakdown', $this->day(['unit_code' => 'ZT-HM2']))->assertSessionHas('success');

        $ready = collect($this->viewProps($admin)['available_haulers'])->whereIn('code', ['ZT-HM', 'ZT-HG', 'ZT-HJ', 'ZT-HM2']);
        $this->assertSame(['ZT-HG' => 'G', 'ZT-HJ' => 'J'], $ready->pluck('parking_code', 'code')->all());
    }

    public function test_send_hauler_to_breakdown_from_ready_and_from_loader_then_return(): void
    {
        $admin = $this->admin();
        $this->seedLoader($admin);
        $this->master('ZT-H1', 'hauler', ['parking_code' => 'J']);
        $this->master('ZT-H2', 'hauler');
        $this->actingAs($admin)->post(self::BASE.'/settings/hauler', $this->day(['loader_code' => 'ZT-E1', 'hauler_code' => 'ZT-H1']));

        $this->actingAs($admin)->post(self::BASE.'/settings/breakdown', $this->day(['unit_code' => 'ZT-H1']))->assertSessionHas('success');
        $this->actingAs($admin)->post(self::BASE.'/settings/breakdown', $this->day(['unit_code' => 'ZT-H2']))->assertSessionHas('success');

        $row = $this->breakdownRows()->where('unit_nrp', 'ZT-H1')->first();
        $this->assertSame(['SERVICE & BD', 'J'], [$row->loader_id, $row->unit_status]);
        $this->assertNull($this->breakdownRows()->where('unit_nrp', 'ZT-H2')->value('unit_status'));
        $this->assertNotNull($this->rows()->where('loader_id', 'ZT-E1')->whereNull('unit_nrp')->first());

        $view = $this->viewProps($admin);
        $this->assertCount(2, collect($view['breakdown'])->whereIn('code', ['ZT-H1', 'ZT-H2']));
        $this->assertSame(['ZT GROUP'], collect($view['groups'])->pluck('name')->all());
        $this->assertNotContains('BREAKDOWN / SERVICE', collect($view['groups'])->pluck('name')->all());
        $this->assertSame(1, $view['summary']['loaders']);
        $this->assertSame(0, $view['summary']['haulers']);

        $this->actingAs($admin)->delete(self::BASE.'/settings/breakdown', $this->day(['unit_code' => 'ZT-H1']))->assertSessionHas('success');
        $this->assertSame(0, $this->breakdownRows()->where('unit_nrp', 'ZT-H1')->count());
        $this->actingAs($admin)->delete(self::BASE.'/settings/breakdown', $this->day(['unit_code' => 'ZT-H1']))->assertSessionHasErrors('form');
    }

    public function test_loader_to_breakdown_releases_its_haulers_and_double_placement_is_rejected(): void
    {
        $admin = $this->admin();
        $this->seedLoader($admin, 'ZT-E1');
        $this->seedLoader($admin, 'ZT-E2');
        $this->master('ZT-H1', 'hauler');
        $this->actingAs($admin)->post(self::BASE.'/settings/hauler', $this->day(['loader_code' => 'ZT-E1', 'hauler_code' => 'ZT-H1']));

        $this->actingAs($admin)->post(self::BASE.'/settings/breakdown', $this->day(['unit_code' => 'ZT-E1']))->assertSessionHas('success');
        $this->assertSame(0, $this->rows()->where('loader_id', 'ZT-E1')->count());
        $this->assertSame(1, $this->breakdownRows()->where('unit_nrp', 'ZT-E1')->count());
        $this->assertContains('ZT-H1', collect($this->viewProps($admin)['available_haulers'])->pluck('code')->all());
        $this->assertNotContains('ZT-E1', collect($this->viewProps($admin)['available_loaders'])->pluck('code')->all());

        $this->actingAs($admin)->post(self::BASE.'/settings/breakdown', $this->day(['unit_code' => 'ZT-E1']))->assertSessionHasErrors('form');
        $this->actingAs($admin)->post(self::BASE.'/settings/loader', $this->day(['loader_code' => 'ZT-E1', 'cluster_name' => 'ZT GROUP']))->assertSessionHasErrors('form');

        $this->actingAs($admin)->post(self::BASE.'/settings/breakdown', $this->day(['unit_code' => 'ZT-H1']));
        $this->actingAs($admin)->post(self::BASE.'/settings/hauler', $this->day(['loader_code' => 'ZT-E2', 'hauler_code' => 'ZT-H1']))->assertSessionHasErrors('form');
        $this->assertSame(0, $this->rows()->where('loader_id', 'ZT-E2')->whereNotNull('unit_nrp')->count());
    }

    public function test_copy_and_clear_include_breakdown_rows(): void
    {
        $admin = $this->admin();
        $this->seedLoader($admin);
        $this->master('ZT-H1', 'hauler', ['parking_code' => 'G']);
        $this->actingAs($admin)->post(self::BASE.'/settings/breakdown', $this->day(['unit_code' => 'ZT-H1']));

        $target = ['date' => self::DATE, 'shift' => 'N'];
        $this->actingAs($admin)->post(self::BASE.'/settings/copy', $target + ['from_date' => self::DATE, 'from_shift' => 'D'])->assertSessionHas('success');
        $this->assertSame('G', $this->breakdownRows('N')->where('unit_nrp', 'ZT-H1')->value('unit_status'));

        $this->actingAs($admin)->delete(self::BASE.'/settings/clear', $target)->assertSessionHas('success');
        $this->assertSame(0, $this->rows('N')->count());
    }

    private function clusters(User $admin, string $shift = 'D'): array
    {
        return collect($this->viewProps($admin, $shift)['groups'])->keyBy('name')->all();
    }

    public function test_create_cluster_with_area_then_add_loader_into_it_and_reject_duplicate_name(): void
    {
        $admin = $this->admin();
        $this->master('ZT-E1', 'loader');

        $this->actingAs($admin)->post(self::BASE.'/settings/cluster', $this->day(['cluster_name' => 'ZT OB', 'cluster_area' => 'Timur']))->assertSessionHas('success');
        $cluster = $this->clusters($admin)['ZT OB'];
        $this->assertSame(['Timur', 0], [$cluster['area'], count($cluster['loaders'])]);
        $this->assertSame(1, $this->viewProps($admin)['summary']['clusters']);

        $this->actingAs($admin)->post(self::BASE.'/settings/cluster', $this->day(['cluster_name' => 'zt ob']))->assertSessionHasErrors('form');
        $this->actingAs($admin)->post(self::BASE.'/settings/loader', $this->day(['loader_code' => 'ZT-E1', 'cluster_name' => 'NO SUCH']))->assertSessionHasErrors('form');
        $this->actingAs($admin)->post(self::BASE.'/settings/loader', $this->day(['loader_code' => 'ZT-E1', 'cluster_name' => 'ZT OB', 'area' => 'DP 1', 'front' => 'F1', 'distance' => 500]))->assertSessionHas('success');

        $loader = $this->rows()->where('loader_id', 'ZT-E1')->first();
        $this->assertSame(['ZT OB', 'DP 1', 'F1', 500], [$loader->group_name, $loader->area, $loader->front, $loader->distance]);
        $this->assertSame('Timur', $this->clusters($admin)['ZT OB']['area']);

        $this->actingAs($admin)->delete(self::BASE.'/settings/loader', $this->day(['loader_code' => 'ZT-E1']))->assertSessionHas('success');
        $this->assertArrayHasKey('ZT OB', $this->clusters($admin));
    }

    public function test_rename_cluster_updates_loader_group_name_and_rejects_duplicate(): void
    {
        $admin = $this->admin();
        $this->seedLoader($admin, 'ZT-E1', 'ZT A');
        $this->actingAs($admin)->post(self::BASE.'/settings/cluster', $this->day(['cluster_name' => 'ZT B']));

        $this->actingAs($admin)->put(self::BASE.'/settings/cluster', $this->day(['cluster_name' => 'ZT A', 'new_cluster_name' => 'ZT B']))->assertSessionHasErrors('form');
        $this->actingAs($admin)->put(self::BASE.'/settings/cluster', $this->day(['cluster_name' => 'ZT A', 'new_cluster_name' => 'ZT C', 'cluster_area' => 'Barat']))->assertSessionHas('success');

        $this->assertSame('ZT C', $this->rows()->where('loader_id', 'ZT-E1')->value('group_name'));
        $clusters = $this->clusters($admin);
        $this->assertSame('Barat', $clusters['ZT C']['area']);
        $this->assertArrayNotHasKey('ZT A', $clusters);
    }

    public function test_remove_cluster_deletes_its_loaders_and_releases_haulers_to_ready(): void
    {
        $admin = $this->admin();
        $this->seedLoader($admin, 'ZT-E1', 'ZT A');
        $this->master('ZT-H1', 'hauler');
        $this->actingAs($admin)->post(self::BASE.'/settings/hauler', $this->day(['loader_code' => 'ZT-E1', 'hauler_code' => 'ZT-H1']));

        $this->actingAs($admin)->delete(self::BASE.'/settings/cluster', $this->day(['cluster_name' => 'ZT A']))->assertSessionHas('success');
        $this->assertSame(0, $this->rows()->count());
        $this->assertContains('ZT-H1', collect($this->viewProps($admin)['available_haulers'])->pluck('code')->all());
        $this->assertContains('ZT-E1', collect($this->viewProps($admin)['available_loaders'])->pluck('code')->all());
        $this->actingAs($admin)->delete(self::BASE.'/settings/cluster', $this->day(['cluster_name' => 'ZT A']))->assertSessionHasErrors('form');
    }

    public function test_copy_and_clear_include_clusters_and_legacy_cluster_without_area_is_listed(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(self::BASE.'/settings/cluster', $this->day(['cluster_name' => 'ZT EMPTY', 'cluster_area' => 'Tengah']));
        FleetAllocation::create(['tanggal' => self::DATE, 'shift' => 1, 'group_name' => 'ZT LEGACY', 'loader_id' => 'ZT-OLD', 'unit_nrp' => null, 'distance' => 0]);

        $clusters = $this->clusters($admin);
        $this->assertSame(['Tengah', null], [$clusters['ZT EMPTY']['area'], $clusters['ZT LEGACY']['area']]);
        $this->assertCount(1, $clusters['ZT LEGACY']['loaders']);

        $target = ['date' => self::DATE, 'shift' => 'N'];
        $copy = ['from_date' => self::DATE, 'from_shift' => 'D'];
        $this->actingAs($admin)->post(self::BASE.'/settings/copy', $target + $copy)->assertSessionHas('success');
        $this->assertSame('Tengah', $this->clusters($admin, 'N')['ZT EMPTY']['area']);
        $this->actingAs($admin)->post(self::BASE.'/settings/copy', $target + $copy)->assertSessionHasErrors('form');

        $this->actingAs($admin)->delete(self::BASE.'/settings/clear', $target)->assertSessionHas('success');
        $this->assertSame([], $this->clusters($admin, 'N'));
        $this->assertArrayHasKey('ZT EMPTY', $this->clusters($admin));
    }
}
