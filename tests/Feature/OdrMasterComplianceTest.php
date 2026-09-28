<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\OdrMasterAktivitas;
use App\Models\OdrMasterArea;
use App\Models\ProsedurKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class OdrMasterComplianceTest extends TestCase
{
    use DatabaseTransactions;

    private const BASE = '/one-reporting/master';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'tmst'.random_int(10000, 99999)]);
    }

    public function test_area_crud_with_unique_name_and_status_toggle(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(self::BASE.'/area', ['nama' => 'AREA MASTER TEST'])
            ->assertRedirect(route('one-reporting.master', ['tab' => 'area']))
            ->assertSessionHas('success', 'Area "AREA MASTER TEST" berhasil ditambahkan.');

        $this->actingAs($admin)->post(self::BASE.'/area', ['nama' => 'AREA MASTER TEST'])
            ->assertSessionHasErrors(['nama' => 'Gagal: nama area sudah ada atau terjadi error.']);

        $area = OdrMasterArea::where('nama', 'AREA MASTER TEST')->firstOrFail();
        $this->actingAs($admin)->put(self::BASE."/area/{$area->id}", ['nama' => 'AREA MASTER TEST', 'aktif' => false])->assertSessionHasNoErrors();
        $this->assertFalse((bool) $area->fresh()->aktif);

        $this->actingAs($admin)->delete(self::BASE."/area/{$area->id}")->assertSessionHas('success', 'Area berhasil dihapus.');
        $this->assertModelMissing($area);
    }

    public function test_aktivitas_stores_selected_procedure_documents(): void
    {
        $doc = ProsedurKerja::create(['jenis' => 'sop', 'kode_dokumen' => 'MST-01', 'judul' => 'SOP Master Test', 'file_dokumen' => '/assets/uploads/prosedur_kerja/mst_test.pdf', 'status' => 'aktif']);

        $this->actingAs($this->admin())->post(self::BASE.'/aktivitas', ['nama' => 'Aktivitas Master Test', 'kategori' => 'GMP', 'dokumen' => [$doc->file_dokumen]])
            ->assertSessionHasNoErrors();

        $aktivitas = OdrMasterAktivitas::where('nama', 'Aktivitas Master Test')->firstOrFail();
        $this->assertSame([$doc->file_dokumen], json_decode($aktivitas->keterangan, true));

        $this->actingAs($this->admin())->get(self::BASE.'?tab=aktivitas')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('rows', fn ($rows) => collect($rows)->contains(fn ($r) => $r['nama'] === 'Aktivitas Master Test' && $r['dokumen_urls'] === ["/one-procedure/prosedur/view/{$doc->id}"])));
    }

    public function test_unit_requires_name_and_operator_is_forbidden(): void
    {
        $this->actingAs($this->admin())->post(self::BASE.'/unit', ['kode' => 'X-1'])->assertSessionHasErrors(['nama' => 'Nama unit wajib diisi.']);

        $operator = User::factory()->create(['role' => 'operator', 'username' => 'tmstop'.random_int(1000, 9999)]);
        $this->actingAs($operator)->get(self::BASE)->assertForbidden();
        $this->actingAs($operator)->post(self::BASE.'/area', ['nama' => 'X'])->assertForbidden();
    }
}
