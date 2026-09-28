<?php

namespace Tests\Feature;

use App\Models\RosterData;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MonitoringManpowerControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->create(['username' => 'tmm'.random_int(10000, 99999), 'role' => 'admin']);
    }

    public function test_category_lock_can_be_stored_and_deleted(): void
    {
        $admin = $this->admin();
        $baselineCount = count(json_decode(Setting::getValue('unit_category_locks', '[]'), true) ?: []);

        $this->actingAs($admin)
            ->post('/one-setting/category-locks', [
                'category' => 'Excavator Support',
                'units' => ['PC2001', 'PC2002'],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $locks = json_decode(Setting::getValue('unit_category_locks', '[]'), true);
        $this->assertCount($baselineCount + 1, $locks);
        $newLock = collect($locks)->firstWhere('category', 'EXCAVATOR SUPPORT');
        $this->assertNotNull($newLock);
        $this->assertSame(['PC2001', 'PC2002'], $newLock['units']);

        $this->actingAs($admin)
            ->delete("/one-setting/category-locks/{$newLock['id']}")
            ->assertSessionHasNoErrors();

        $locksAfterDelete = json_decode(Setting::getValue('unit_category_locks', '[]'), true) ?: [];
        $this->assertCount($baselineCount, $locksAfterDelete);
    }

    public function test_monitoring_manpower_pulls_password_checkpoint_from_pw_cp(): void
    {
        DB::statement("CREATE TABLE IF NOT EXISTS pw_speakup_cp (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tanggal DATE NOT NULL,
            shift VARCHAR(5) NOT NULL DEFAULT '1',
            password VARCHAR(200) NOT NULL DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_tgl_shift_pw (tanggal, shift)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        DB::table('pw_speakup_cp')->updateOrInsert(
            ['tanggal' => '2030-01-15', 'shift' => '1'],
            ['password' => 'RAHASIA123']
        );

        $this->actingAs($this->admin())
            ->get('/one-setting/monitoring-manpower?date=2030-01-15&shift=D')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OneSetting/MonitoringManpower')
                ->where('passwordCheckPoint', 'RAHASIA123')
            );
    }

    public function test_reset_data_clears_roster_for_date_and_shift_only(): void
    {
        RosterData::create(['nrp' => 'MM1', 'tanggal' => '2030-02-01', 'kode' => 'D', 'unit' => 'HD78300', 'csa_type' => 'CSA GENERAL']);
        RosterData::create(['nrp' => 'MM2', 'tanggal' => '2030-02-01', 'kode' => 'N', 'unit' => 'HD78301', 'csa_type' => 'CSA GENERAL']);

        $this->actingAs($this->admin())
            ->post('/one-setting/roster/clear', ['date' => '2030-02-01', 'shift' => 'D'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseMissing('roster_data', ['nrp' => 'MM1', 'tanggal' => '2030-02-01', 'kode' => 'D']);
        $this->assertDatabaseHas('roster_data', ['nrp' => 'MM2', 'tanggal' => '2030-02-01', 'kode' => 'N']);
    }
}
