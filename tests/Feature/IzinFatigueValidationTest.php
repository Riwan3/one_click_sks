<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinFatigueValidationTest extends TestCase
{
    use RefreshDatabase;

    private function baseUser(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'admin'.random_int(10000, 99999)]);
    }

    public function test_it_saves_even_when_jam_akhir_is_empty(): void
    {
        $user = $this->baseUser();

        $this->actingAs($user)->post('/one-dms/izin-fatigue/store', [
            'tanggal' => '2026-09-05',
            'shift' => 'Shift 1',
            'nrp' => '260110',
            'nama_operator' => 'Test Operator',
            'jam_mulai' => '12:00',
            'jam_akhir' => '',
            'total_jam' => '',
        ])->assertSessionDoesntHaveErrors()->assertRedirect();

        $this->assertDatabaseHas('izin_fatigue_data', [
            'nrp' => '260110',
            'jam_mulai' => '12:00',
            'jam_akhir' => '',
        ]);
    }

    public function test_it_saves_even_when_all_jam_fields_are_empty(): void
    {
        $user = $this->baseUser();

        $this->actingAs($user)->post('/one-dms/izin-fatigue/store', [
            'tanggal' => '2026-09-05',
            'shift' => 'Shift 1',
            'nrp' => '260111',
            'nama_operator' => 'Test Operator',
            'jam_mulai' => '',
            'jam_akhir' => '',
            'total_jam' => '',
        ])->assertSessionDoesntHaveErrors()->assertRedirect();

        $this->assertDatabaseHas('izin_fatigue_data', ['nrp' => '260111']);
    }

    public function test_it_saves_when_jam_fields_are_filled(): void
    {
        $user = $this->baseUser();

        $this->actingAs($user)->post('/one-dms/izin-fatigue/store', [
            'tanggal' => '2026-09-05',
            'shift' => 'Shift 1',
            'nrp' => '260112',
            'nama_operator' => 'Test Operator',
            'jam_mulai' => '12:00',
            'jam_akhir' => '13:00',
            'total_jam' => '1 Jam',
        ])->assertSessionDoesntHaveErrors()->assertRedirect();

        $this->assertDatabaseHas('izin_fatigue_data', [
            'nrp' => '260112',
            'jam_mulai' => '12:00',
            'jam_akhir' => '13:00',
        ]);
    }

    public function test_it_rejects_invalid_jam_format(): void
    {
        $user = $this->baseUser();

        $this->actingAs($user)->post('/one-dms/izin-fatigue/store', [
            'tanggal' => '2026-09-05',
            'shift' => 'Shift 1',
            'nrp' => '260113',
            'nama_operator' => 'Test Operator',
            'jam_mulai' => 'bukan-jam',
            'jam_akhir' => '',
            'total_jam' => '',
        ])->assertSessionHasErrors('jam_mulai');

        $this->assertDatabaseMissing('izin_fatigue_data', ['nrp' => '260113']);
    }
}
