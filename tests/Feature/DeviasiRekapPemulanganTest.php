<?php

namespace Tests\Feature;

use App\Models\DeviasiRekapPemulangan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DeviasiRekapPemulanganTest extends TestCase
{
    use DatabaseTransactions;

    private function makeRekap(): DeviasiRekapPemulangan
    {
        return DeviasiRekapPemulangan::create([
            'nrp' => '21001234',
            'nama' => 'Test Operator',
            'jabatan' => 'Operator Hauler',
            'tanggal' => now()->toDateString(),
            'shift' => 'D',
            'jam' => '08:30:00',
            'alasan' => 'Sakit kepala',
            'keterangan' => 'Sakit kepala',
            'konfirmasi' => 'Disetujui GL',
        ]);
    }

    public function test_authorized_roles_can_access_rekap_pemulangan(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\CheckSessionSecurity::class);
        $suffix = (string) random_int(1000, 9999);
        $base = '/one-power/deviasi-operator/rekap-pemulangan';

        foreach (['admin', 'pimpinan', 'group-leader', 'medic'] as $role) {
            $user = User::factory()->create(['role' => $role, 'username' => 'rk'.$role.$suffix]);
            $rekap = $this->makeRekap();

            $this->actingAs($user)->get($base)->assertOk();
            $this->actingAs($user)->get("$base/search-employee?keyword=x")->assertOk();
            $this->actingAs($user)->get("$base/template")->assertOk();

            $this->actingAs($user)->post($base, [
                'nrp' => '21009999',
                'nama' => 'Operator Baru',
                'jabatan' => 'Operator Excavator',
                'tanggal' => now()->toDateString(),
                'shift' => 'N',
                'jam' => '20:00',
                'keterangan' => 'Keluarga sakit',
                'konfirmasi' => 'Foreman',
            ])->assertSessionHasNoErrors()->assertRedirect();

            $this->assertDatabaseHas('deviasi_rekap_pemulangan', ['nrp' => '21009999']);

            $this->actingAs($user)->put("$base/{$rekap->id}", [
                'nrp' => $rekap->nrp,
                'nama' => 'Nama Diubah',
                'jabatan' => $rekap->jabatan,
                'tanggal' => $rekap->tanggal->format('Y-m-d'),
                'shift' => 'D',
                'jam' => '09:00',
                'keterangan' => 'Update keterangan',
            ])->assertSessionHasNoErrors()->assertRedirect();

            $this->assertDatabaseHas('deviasi_rekap_pemulangan', ['id' => $rekap->id, 'nama' => 'Nama Diubah']);

            $this->actingAs($user)->delete("$base/{$rekap->id}")->assertRedirect();
            $this->assertDatabaseMissing('deviasi_rekap_pemulangan', ['id' => $rekap->id]);
        }
    }

    public function test_unauthorized_role_forbidden(): void
    {
        $suffix = (string) random_int(1000, 9999);
        $user = User::factory()->create(['role' => 'operator', 'username' => 'op'.$suffix]);
        $base = '/one-power/deviasi-operator/rekap-pemulangan';

        $this->actingAs($user)->get($base)->assertForbidden();
    }
}
