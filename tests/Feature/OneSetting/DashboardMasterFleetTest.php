<?php

namespace Tests\Feature\OneSetting;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\FleetMasterUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
}
