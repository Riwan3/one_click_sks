<?php

namespace Tests\Feature;

use App\Models\OperatorUnfit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OperatorUnfitControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->create(['username' => 'tou'.random_int(10000, 99999), 'role' => 'admin']);
    }

    public function test_store_defaults_perusahaan_when_left_blank(): void
    {
        $nrp = 'OU'.random_int(10000, 99999);

        $this->actingAs($this->admin())
            ->post('/one-power/deviasi-operator/unfit', [
                'nrp' => $nrp,
                'nama' => 'Karyawan Unfit',
                'tgl_mulai_unfit' => now()->toDateString(),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('operator_unfits', [
            'nrp' => $nrp,
            'perusahaan' => 'PT. PUTRA PERKASA ABADI',
        ]);
    }

    public function test_update_defaults_perusahaan_when_cleared(): void
    {
        $admin = $this->admin();
        $unfit = OperatorUnfit::create([
            'nrp' => 'OU'.random_int(10000, 99999),
            'nama' => 'Karyawan Unfit',
            'perusahaan' => 'PT. LAIN',
            'tgl_mulai_unfit' => now(),
        ]);

        $this->actingAs($admin)
            ->put("/one-power/deviasi-operator/unfit/{$unfit->id}", [
                'nrp' => $unfit->nrp,
                'nama' => $unfit->nama,
                'tgl_mulai_unfit' => now()->toDateString(),
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('PT. PUTRA PERKASA ABADI', $unfit->fresh()->perusahaan);
    }
}
