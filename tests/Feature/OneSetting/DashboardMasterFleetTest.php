<?php

namespace Tests\Feature\OneSetting;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\FleetAllocation;
use App\Models\FleetMasterUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DashboardMasterFleetTest extends TestCase
{
    use DatabaseTransactions;

    public function test_master_fleet_unit_card_counts_active_units_from_master_unit(): void
    {
        $this->withoutMiddleware(CheckSessionSecurity::class);
        $user = User::factory()->create(['role' => 'admin', 'username' => 'tmp'.random_int(10000, 99999)]);
        $before = FleetMasterUnit::where('is_active', true)->count();

        FleetMasterUnit::create(['code' => 'ZT-DASH-L', 'unit_type' => 'loader', 'is_active' => true]);
        FleetMasterUnit::create(['code' => 'ZT-DASH-H', 'unit_type' => 'hauler', 'is_active' => true]);
        FleetMasterUnit::create(['code' => 'ZT-DASH-X', 'unit_type' => 'hauler', 'is_active' => false]);

        $this->actingAs($user)->get('/one-setting/dashboard')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('stats.totalFleetUnits', $before + 2));
    }

    public function test_alokasi_fleet_card_counts_distinct_operating_units_without_breakdown(): void
    {
        $this->withoutMiddleware(CheckSessionSecurity::class);
        $user = User::factory()->create(['role' => 'admin', 'username' => 'tmp'.random_int(10000, 99999)]);
        $date = '2031-05-05';

        $row = fn (int $shift, string $group, string $loader, ?string $unit) => FleetAllocation::create([
            'tanggal' => $date, 'shift' => $shift, 'group_name' => $group, 'loader_id' => $loader, 'unit_nrp' => $unit,
        ]);
        $row(1, 'OB', 'ZT-E1', 'ZT-H1');
        $row(1, 'OB', 'ZT-E1', 'ZT-H2');
        $row(1, 'OB', 'ZT-E2', null);
        $row(2, 'OB', 'ZT-E1', 'ZT-H1');
        $row(1, 'BREAKDOWN / SERVICE', 'SERVICE & BD', 'ZT-H9');

        $this->actingAs($user)->get('/one-setting/dashboard?date='.$date)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('stats.totalFleetAllocations', 4));
    }

    public function test_roster_manpower_card_counts_only_operators_assigned_in_setting_manpower(): void
    {
        $this->withoutMiddleware(CheckSessionSecurity::class);
        $user = User::factory()->create(['role' => 'admin', 'username' => 'tmp'.random_int(10000, 99999)]);
        $date = '2031-06-06';
        $unitIds = DB::table('v2_mp_units')->orderBy('id')->limit(3)->pluck('id')->all();
        $this->assertCount(3, $unitIds, 'Butuh minimal tiga unit di Master CSA.');

        DB::table('employees')->insert([
            ['nrp' => '9990101', 'name' => 'Tes Operator', 'jabatan_ss6' => 'OPERATOR HD 785'],
            ['nrp' => '9990102', 'name' => 'Tes Driver', 'jabatan_ss6' => 'DRIVER PPA111'],
            ['nrp' => '9990103', 'name' => 'Tes GL', 'jabatan_ss6' => 'GROUP LEADER PRODUKSI'],
        ]);
        $day = DB::table('v2_mp_settings')->insertGetId(['work_date' => $date, 'shift' => 'D']);
        $night = DB::table('v2_mp_settings')->insertGetId(['work_date' => $date, 'shift' => 'N']);
        foreach ([[$day, '9990101', 0], [$day, '9990102', 1], [$day, '9990103', 2], [$night, '9990101', 0]] as [$settingId, $nrp, $unitIndex]) {
            DB::table('v2_mp_assignments')->insert(['setting_id' => $settingId, 'unit_id' => $unitIds[$unitIndex], 'nrp' => $nrp, 'source' => 'manual']);
        }

        $this->actingAs($user)->get('/one-setting/dashboard?date='.$date)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('stats.totalManpowerRoster', 1));
    }
}
