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
        $this->master($loader, 'loader', ['default_group' => $group, 'default_area' => 'PIT A', 'default_distance' => 800]);
        $this->actingAs($admin)->post(self::BASE.'/settings/loader', $this->day(['loader_code' => $loader]))->assertSessionHas('success');
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

        $payload = ['categories' => $preview->map(fn ($c) => ['name' => $c['name'], 'type' => $c['type']])->all()];
        $this->actingAs($admin)->post(self::BASE.'/master/pull', $payload)->assertSessionHas('success');
        $added = FleetMasterUnit::count();
        $this->assertGreaterThan(0, $added);
        $this->assertSame(0, FleetMasterUnit::whereNotIn('unit_type', ['loader', 'hauler'])->count());

        $this->actingAs($admin)->post(self::BASE.'/master/pull', $payload)->assertSessionHas('success');
        $this->assertSame($added, FleetMasterUnit::count());
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

    public function test_add_loader_uses_master_defaults_then_assign_move_status_and_release_hauler(): void
    {
        $admin = $this->admin();
        $this->seedLoader($admin, 'ZT-E1');
        $this->seedLoader($admin, 'ZT-E2');
        $this->master('ZT-H1', 'hauler');

        $loader = $this->rows()->where('loader_id', 'ZT-E1')->first();
        $this->assertSame(['ZT GROUP', 'PIT A', 800], [$loader->group_name, $loader->area, $loader->distance]);

        $this->actingAs($admin)->post(self::BASE.'/settings/hauler', $this->day(['loader_code' => 'ZT-E1', 'hauler_code' => 'ZT-H1']))->assertSessionHas('success');
        $this->assertSame('M', $this->rows()->where('unit_nrp', 'ZT-H1')->value('unit_status'));
        $this->assertNull($this->rows()->where('loader_id', 'ZT-E1')->whereNull('unit_nrp')->first());

        $this->actingAs($admin)->put(self::BASE.'/settings/hauler', $this->day(['hauler_code' => 'ZT-H1', 'status' => 'G']))->assertSessionHas('success');
        $this->assertSame('G', $this->rows()->where('unit_nrp', 'ZT-H1')->value('unit_status'));

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
        $this->actingAs($admin)->post(self::BASE.'/settings/loader', $this->day(['loader_code' => 'ZT-E1']))->assertSessionHasErrors('form');
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
}
