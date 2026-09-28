<?php

namespace Tests\Feature;

use App\Models\FleetAllocation;
use App\Models\RosterData;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OneSettingDashboardControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->create(['username' => 'tod'.random_int(10000, 99999), 'role' => 'admin']);
    }

    private function allocation(string $tanggal, int $shift, string $unitNrp): FleetAllocation
    {
        return FleetAllocation::create([
            'tanggal' => $tanggal,
            'shift' => $shift,
            'group_name' => 'BARAT',
            'loader_id' => 'E51202',
            'unit_nrp' => $unitNrp,
            'unit_status' => 'M',
            'distance' => 100,
        ]);
    }

    public function test_dashboard_groups_fleet_import_history_by_date_and_shift(): void
    {
        $this->allocation('2030-03-01', 1, 'E51202');
        $this->allocation('2030-03-01', 1, 'E51203');
        $this->allocation('2030-03-01', 2, 'E51204');

        $this->actingAs($this->admin())
            ->get('/one-setting/dashboard?history_month=2030-03')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OneSetting/Dashboard')
                ->has('fleetImportHistory', fn (Assert $history) => $history
                    ->where('0.tanggal', '2030-03-01')
                    ->where('0.shift', 1)
                    ->where('0.jumlah_unit', 2)
                    ->etc()
                )
            );
    }

    public function test_delete_fleet_batch_removes_only_that_date_and_shift(): void
    {
        $this->allocation('2030-03-05', 1, 'E51202');
        $this->allocation('2030-03-05', 2, 'E51203');

        $this->actingAs($this->admin())
            ->post('/one-setting/fleet/delete', ['date' => '2030-03-05', 'shift' => 1])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseMissing('fleet_allocations', ['tanggal' => '2030-03-05', 'shift' => 1]);
        $this->assertDatabaseHas('fleet_allocations', ['tanggal' => '2030-03-05', 'shift' => 2]);
    }

    public function test_dashboard_groups_manpower_import_history_by_date_and_shift(): void
    {
        RosterData::create(['nrp' => 'H1', 'tanggal' => '2030-04-01', 'kode' => 'D', 'unit' => 'HD78300', 'csa_type' => 'CSA GENERAL']);
        RosterData::create(['nrp' => 'H2', 'tanggal' => '2030-04-01', 'kode' => 'D', 'unit' => 'HD78301', 'csa_type' => 'CSA GENERAL']);
        RosterData::create(['nrp' => 'H3', 'tanggal' => '2030-04-01', 'kode' => 'N', 'unit' => 'HD78302', 'csa_type' => 'CSA GENERAL']);

        $this->actingAs($this->admin())
            ->get('/one-setting/dashboard?history_month=2030-04')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OneSetting/Dashboard')
                ->has('manpowerImportHistory', fn (Assert $history) => $history
                    ->where('0.tanggal', '2030-04-01')
                    ->where('0.shift', 'D')
                    ->where('0.jumlah_unit', 2)
                    ->etc()
                )
            );
    }

    public function test_history_month_filter_excludes_other_months(): void
    {
        $this->allocation('2030-05-01', 1, 'E51202');
        $this->allocation('2030-06-01', 1, 'E51203');

        $this->actingAs($this->admin())
            ->get('/one-setting/dashboard?history_month=2030-05')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OneSetting/Dashboard')
                ->has('fleetImportHistory', 1)
                ->where('fleetImportHistory.0.tanggal', '2030-05-01')
                ->where('historyMonth', '2030-05')
            );
    }

    public function test_reset_manpower_batch_removes_only_that_date_and_shift(): void
    {
        RosterData::create(['nrp' => 'H4', 'tanggal' => '2030-04-05', 'kode' => 'D', 'unit' => 'HD78300', 'csa_type' => 'CSA GENERAL']);
        RosterData::create(['nrp' => 'H5', 'tanggal' => '2030-04-05', 'kode' => 'N', 'unit' => 'HD78301', 'csa_type' => 'CSA GENERAL']);

        $this->actingAs($this->admin())
            ->post('/one-setting/roster/clear', ['date' => '2030-04-05', 'shift' => 'D'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseMissing('roster_data', ['tanggal' => '2030-04-05', 'kode' => 'D']);
        $this->assertDatabaseHas('roster_data', ['tanggal' => '2030-04-05', 'kode' => 'N']);
    }
}
