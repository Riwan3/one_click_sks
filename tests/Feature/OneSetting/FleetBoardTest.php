<?php

namespace Tests\Feature\OneSetting;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\FleetAllocation;
use App\Models\FleetArea;
use App\Models\FleetCluster;
use App\Models\FleetMasterUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class FleetBoardTest extends TestCase
{
    use DatabaseTransactions;

    private const DATE = '2031-03-04';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'tmp'.random_int(10000, 99999)]);
    }

    private function allocate(string $date, int $shift, string $group, string $loader, ?string $hauler, string $status = 'M'): void
    {
        FleetAllocation::create([
            'tanggal' => $date, 'shift' => $shift, 'group_name' => $group, 'loader_id' => $loader,
            'area' => 'DISP A', 'front' => 'F1', 'refueling_time' => '10:00', 'unit_nrp' => $hauler,
            'unit_status' => $hauler ? $status : null, 'distance' => 800,
        ]);
    }

    private function seedDay(string $date, int $shift): void
    {
        $this->allocate($date, $shift, 'EWT', 'E6101', 'HD1001');
        $this->allocate($date, $shift, 'EWT', 'E6101', 'HD1002', 'G');
        $this->allocate($date, $shift, 'EWT', 'E6102', null);
        $this->allocate($date, $shift, 'BREAKDOWN / SERVICE', 'BD', 'HD1003', 'J');
    }

    private function seedClusters(string $date, int $shift): void
    {
        FleetArea::create(['tanggal' => $date, 'shift' => $shift, 'name' => 'Timur']);
        FleetArea::create(['tanggal' => $date, 'shift' => $shift, 'name' => 'Utara']);
        FleetCluster::create(['tanggal' => $date, 'shift' => $shift, 'name' => 'EWT', 'area' => 'Timur', 'sort_order' => 1]);
        FleetCluster::create(['tanggal' => $date, 'shift' => $shift, 'name' => 'CLUSTER KOSONG', 'area' => null, 'sort_order' => 2]);
        $this->allocate($date, $shift, 'COAL GETTING', 'E6103', 'HD1004');
    }

    public function test_fleet_board_renders_sheet_for_same_date_and_shift(): void
    {
        $this->seedDay(self::DATE, 1);
        $this->seedClusters(self::DATE, 1);

        $this->actingAs($this->admin())->get('/one-setting/setting-board?date='.self::DATE.'&shift=D')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('OneSetting/SettingBoard')
                ->where('fleetPreview.sheet.date', self::DATE)
                ->where('fleetPreview.sheet.shift', 'D')
                ->where('fleetPreview.sheet.total_hauler', 3)
                ->where('fleetPreview.sheet.areas.0.name', 'AREA TIMUR')
                ->where('fleetPreview.sheet.areas.0.clusters.0.name', 'EWT')
                ->where('fleetPreview.sheet.areas.0.clusters.0.loaders.0.code', 'E6101')
                ->where('fleetPreview.sheet.areas.0.clusters.0.loaders.0.dumping', 'DISP A')
                ->where('fleetPreview.sheet.areas.0.clusters.0.loaders.0.units.1.status', 'G')
                ->where('fleetPreview.sheet.areas.0.clusters.0.loaders.1.units', [])
                ->where('fleetPreview.sheet.areas.1.name', 'AREA UTARA')
                ->where('fleetPreview.sheet.areas.1.clusters', [])
                ->where('fleetPreview.sheet.areas.2.name', 'TANPA AREA')
                ->where('fleetPreview.sheet.areas.2.clusters.1.name', 'CLUSTER KOSONG')
                ->where('fleetPreview.sheet.areas.2.clusters.1.loaders', [])
                ->where('fleetPreview.sheet.areas.2.clusters.0.name', 'COAL GETTING')
                ->where('fleetPreview.sheet.breakdown.units.0.code', 'HD1003'));
    }

    public function test_fleet_board_lists_ready_haulers_and_unit_summary(): void
    {
        $this->seedDay(self::DATE, 1);
        $this->seedClusters(self::DATE, 1);
        foreach ([['HD1001', 'M'], ['HD1003', 'M'], ['HD9001', 'M'], ['HD9002', 'G'], ['HD9003', 'J']] as [$code, $parking]) {
            FleetMasterUnit::create(['code' => $code, 'unit_type' => 'hauler', 'parking_code' => $parking, 'is_active' => true]);
        }
        FleetMasterUnit::create(['code' => 'HD9004', 'unit_type' => 'hauler', 'parking_code' => 'M', 'is_active' => false]);

        $this->actingAs($this->admin())->get('/one-setting/setting-board?date='.self::DATE.'&shift=D')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('fleetPreview.sheet.ready.monte', fn ($units) => collect($units)->pluck('code')->contains('HD9001')
                    && ! collect($units)->pluck('code')->intersect(['HD1001', 'HD1003', 'HD9004'])->count())
                ->where('fleetPreview.sheet.ready.gibson', fn ($units) => collect($units)->pluck('code')->intersect(['HD9002', 'HD9003'])->count() === 2)
                ->where('fleetPreview.sheet.summary', ['ob' => 2, 'coal' => 1, 'breakdown' => 1, 'total' => 4]));
    }

    public function test_fleet_board_is_empty_for_other_shift_or_invalid_input(): void
    {
        $this->seedDay(self::DATE, 1);
        $admin = $this->admin();

        $this->actingAs($admin)->get('/one-setting/setting-board?date='.self::DATE.'&shift=N')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('fleetPreview', null));
        $this->actingAs($admin)->get('/one-setting/setting-board?date=bukan-tanggal&shift=X')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('fleetPreview', null));
    }

    public function test_fleet_monitoring_summarizes_each_date_and_shift(): void
    {
        $this->seedDay(self::DATE, 1);
        $this->allocate(self::DATE, 2, 'EWT', 'E6101', 'HD1001');
        $this->allocate(self::DATE, 2, 'COAL GETTING', 'E6102', 'HD1001');

        $this->actingAs($this->admin())->get('/one-setting/manpower/monitoring?type=fleet&month=2031-03&page=1')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('OneSetting/ManpowerV201/Monitoring')
                ->where('filters.type', 'fleet')
                ->where('monitoring.counts.kosong', 1)
                ->where('monitoring.counts.dobel', 1)
                ->where('monitoring.rows.0.shift', 'D')
                ->where('monitoring.rows.0.status', 'kosong')
                ->where('monitoring.rows.0.loaders', 2)
                ->where('monitoring.rows.0.haulers', 3)
                ->where('monitoring.rows.0.loaders_empty', 1)
                ->where('monitoring.rows.1.status', 'dobel')
                ->where('monitoring.rows.1.doubles', 1));
    }

    public function test_fleet_monitoring_status_filter_and_default_type_stays_manpower(): void
    {
        $this->seedDay(self::DATE, 1);
        $admin = $this->admin();

        $this->actingAs($admin)->get('/one-setting/manpower/monitoring?type=fleet&month=2031-03&page=1&status=terisi')
            ->assertInertia(fn (AssertableInertia $page) => $page->has('monitoring.rows', 0));

        $this->actingAs($admin)->get('/one-setting/manpower/monitoring?month=2031-03')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('filters.type', 'manpower')->has('monitoring.counts.final'));
    }

    public function test_fleet_monitoring_is_forbidden_without_permission(): void
    {
        $operator = User::factory()->create(['role' => 'operator', 'username' => 'tmpop'.random_int(10000, 99999)]);

        $this->actingAs($operator)->get('/one-setting/manpower/monitoring?type=fleet')->assertForbidden();
    }
}
