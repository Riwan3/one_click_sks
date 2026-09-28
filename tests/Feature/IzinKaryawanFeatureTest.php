<?php

namespace Tests\Feature;

use App\Enums\IzinJenis;
use App\Enums\IzinStatus;
use App\Models\Employee;
use App\Models\IzinKaryawan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IzinKaryawanFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_renders_and_returns_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'admin_' . uniqid()]);

        $izin = IzinKaryawan::create([
            'jenis_izin' => IzinJenis::Izin,
            'no_surat' => '001/SI/PPA-ADRO/IX/2026',
            'nama' => 'Budi Santoso',
            'nrp' => '12345678',
            'jabatan' => 'Operator Excavator',
            'departemen' => 'Produksi',
            'alasan' => 'Keperluan keluarga',
            'tgl_berangkat' => '2026-09-22',
            'tgl_kembali' => '2026-09-24',
            'status' => IzinStatus::Awaiting,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get('/one-power/deviasi-operator/izin-karyawan');
        $response->assertOk();
    }

    public function test_get_next_surat_number(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'username' => 'admin_' . uniqid()]);

        $response = $this->actingAs($user)->getJson('/one-power/deviasi-operator/izin-karyawan/next-surat-number?jenis_izin=izin');
        $response->assertOk();
        $response->assertJsonStructure(['no_surat']);
    }

    public function test_store_and_approve_izin(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'admin_' . uniqid()]);

        $file = UploadedFile::fake()->create('surat.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)->post('/one-power/deviasi-operator/izin-karyawan', [
            'jenis_izin' => 'izin',
            'nrp' => '12345',
            'nama' => 'Karyawan Uji',
            'jabatan' => 'Operator',
            'departemen' => 'Produksi',
            'alasan' => 'Urusan pribadi',
            'tgl_berangkat' => '2026-09-22',
            'tgl_kembali' => '2026-09-23',
            'file_bukti' => $file,
        ]);

        $response->assertRedirect('/one-power/deviasi-operator/izin-karyawan');

        $izin = IzinKaryawan::where('nrp', '12345')->first();
        $this->assertNotNull($izin);
        $this->assertEquals('Urusan pribadi', $izin->alasan);

        // Test approve
        $this->actingAs($admin)->post("/one-power/deviasi-operator/izin-karyawan/{$izin->id}/approve")
            ->assertRedirect();

        $this->assertEquals(IzinStatus::Approved, $izin->fresh()->status);
    }
}
