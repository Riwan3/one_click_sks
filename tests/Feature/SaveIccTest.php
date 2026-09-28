<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaveIccTest extends TestCase
{
    use RefreshDatabase;

    private function baseUser(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'admin'.random_int(10000, 99999)]);
    }

    public function test_it_creates_new_smoking_deviasi_record(): void
    {
        $user = $this->baseUser();

        $this->actingAs($user)->post('/one-dms/data-icc/update', [
            'category' => 'smoking-deviasi',
            'tanggal' => '2026-09-05',
            'shift' => 'Shift 1',
            'nrp' => '260110',
            'nama_operator' => 'Test Operator',
            'unit' => 'HD001',
            'area' => 'Pit A',
            'group_leader' => 'GL Test',
            'status' => 'Open',
            'evidence' => 'evidence.jpg',
            'fu' => 'Sudah ditegur',
        ])->assertSessionDoesntHaveErrors()->assertRedirect();

        $this->assertDatabaseHas('smoking_deviasi', [
            'nrp' => '260110',
            'unit' => 'HD001',
        ]);
    }

    public function test_it_creates_new_deviasi_fatigue_record_with_jenis_deviasi(): void
    {
        $user = $this->baseUser();

        $this->actingAs($user)->post('/one-dms/data-icc/update', [
            'category' => 'deviasi-fatigue',
            'tanggal' => '2026-09-05',
            'shift' => 'Shift 1',
            'nrp' => '260111',
            'nama_operator' => 'Test Operator',
            'unit' => 'HD002',
            'area' => 'Pit B',
            'waktu' => '13:00',
            'deviasi_ke' => 1,
            'intervensi' => 'Ditegur via radio',
            'group_leader' => 'GL Test',
            'status' => 'Open',
        ])->assertSessionDoesntHaveErrors()->assertRedirect();

        $this->assertDatabaseHas('fatigue_data', [
            'nrp' => '260111',
            'jenis_deviasi' => 'Driver Fatigue',
        ]);
    }

    public function test_it_creates_new_adas_record_with_mapped_jenis_deviasi(): void
    {
        $user = $this->baseUser();

        $this->actingAs($user)->post('/one-dms/data-icc/update', [
            'category' => 'driver-distraction',
            'tanggal' => '2026-09-05',
            'shift' => 'Shift 2',
            'nrp' => '260112',
            'nama_operator' => 'Test Operator',
            'unit' => 'HD003',
            'area' => 'Pit C',
            'waktu' => '09:30',
            'deviasi_ke' => 2,
            'intervensi' => 'Validasi lapangan',
            'group_leader' => 'GL Test',
            'status' => 'Open',
        ])->assertSessionDoesntHaveErrors()->assertRedirect();

        $this->assertDatabaseHas('fatigue_adas_alarm', [
            'nrp' => '260112',
            'jenis_deviasi' => 'Driver Distraction',
            'deviasi_ke' => 2,
        ]);
    }

    public function test_it_updates_existing_record_instead_of_creating_new_one(): void
    {
        $user = $this->baseUser();
        $id = \Illuminate\Support\Facades\DB::table('smoking_deviasi')->insertGetId([
            'tanggal' => '2026-09-01',
            'shift' => 'Shift 1',
            'unit' => 'HD999',
            'nama_operator' => 'Old Name',
            'nrp' => '999999',
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->post('/one-dms/data-icc/update', [
            'id' => $id,
            'category' => 'smoking-deviasi',
            'tanggal' => '2026-09-01',
            'shift' => 'Shift 1',
            'nrp' => '999999',
            'nama_operator' => 'Updated Name',
            'status' => 'Close',
        ])->assertSessionDoesntHaveErrors()->assertRedirect();

        $this->assertDatabaseCount('smoking_deviasi', 1);
        $this->assertDatabaseHas('smoking_deviasi', [
            'id' => $id,
            'nama_operator' => 'Updated Name',
            'status' => 'Close',
        ]);
    }
}
