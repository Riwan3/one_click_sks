<?php

namespace Tests\Feature;

use App\Models\OpdMinimalGrade;
use App\Models\OpdPengajuanGrade;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OpdGradeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['opd_unlocked_at' => time()]); // Pengajuan Grade terbuka, kecuali test kunci di bawah
    }

    public function test_pengajuan_grade_is_locked_until_authorization_password_is_entered(): void
    {
        OpdPengajuanGrade::create(['nrp' => 'OPD6', 'tahun' => (int) date('Y'), 'termin' => 1, 'nama' => 'Fani']);
        $admin = $this->admin();
        $this->withSession(['opd_unlocked_at' => 0]);

        $this->actingAs($admin)->get('/one-academy/opd/pengajuan-grade')
            ->assertInertia(fn (Assert $page) => $page->where('isLocked', true)->where('rows', [])->where('usedTermins', []));
        $this->actingAs($admin)->post('/one-academy/opd/pengajuan-grade', ['nrp' => 'X', 'tahun' => 2026, 'termin' => 1])
            ->assertRedirect(route('one-academy.opd.pengajuan.index'));
        $this->actingAs($admin)->getJson('/one-academy/opd/pengajuan-grade/search-employee?q=ab')->assertForbidden();

        $this->actingAs($admin)->post('/one-academy/opd/pengajuan-grade/unlock', ['password' => 'salah'])->assertSessionHasErrors('password');
        $this->actingAs($admin)->post('/one-academy/opd/pengajuan-grade/unlock', ['password' => 'admin123'])->assertSessionHasNoErrors();

        $this->actingAs($admin)->get('/one-academy/opd/pengajuan-grade')
            ->assertInertia(fn (Assert $page) => $page->where('isLocked', false)->where('rows', fn ($rows) => collect($rows)->contains('nrp', 'OPD6')));

        $this->actingAs($admin)->post('/one-academy/opd/pengajuan-grade/lock');
        $this->actingAs($admin)->get('/one-academy/opd/pengajuan-grade')->assertInertia(fn (Assert $page) => $page->where('isLocked', true));
    }

    private function admin(): User
    {
        return User::factory()->create(['username' => 'opd'.random_int(10000, 99999), 'role' => 'admin']);
    }

    private function monitoringRow(array $overrides = []): void
    {
        DB::table('odp_mg_grade_v4')->insert([
            'nrp' => 'OPD1', 'nama' => 'Budi', 'posisi' => 'OPERATOR HD 785', 'grade_now' => '1C',
            'efektif_grade' => '2020-01-01', 'created_at' => now(), 'updated_at' => now(),
            ...$overrides,
        ]);
    }

    public function test_beranda_flags_under_grade_against_minimal_grade_matrix(): void
    {
        OpdMinimalGrade::create(['unit' => 'HD785', 'grade_min_max' => '1E - 2A', 'grade_min' => '1E']);
        $this->monitoringRow();

        $this->actingAs($this->admin())
            ->get('/one-academy/opd/beranda')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OneAcademy/Opd/Beranda')
                ->where('hasMatrix', true)
                ->where('rows.0.nrp', 'OPD1')
                ->where('rows.0.status', 'under')
                ->where('rows.0.gradeRange', '1E - 2A')
                ->where('rows.0.isOverMinYears', true)
            );
    }

    public function test_ajukan_copies_monitoring_data_and_rejects_duplicate_termin(): void
    {
        $this->monitoringRow(['sub_area' => 'SITE-A']);
        $admin = $this->admin();

        $this->actingAs($admin)->post('/one-academy/opd/pengajuan-grade/ajukan', ['nrp' => 'OPD1', 'termin' => 2])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('opd_pengajuan_grade', ['nrp' => 'OPD1', 'termin' => 2, 'tahun' => (int) date('Y'), 'site' => 'SITE-A', 'pos_saat_ini' => 'OPERATOR HD 785']);

        $this->actingAs($admin)->post('/one-academy/opd/pengajuan-grade/ajukan', ['nrp' => 'OPD1', 'termin' => 2])
            ->assertSessionHas('error');
        $this->assertSame(1, OpdPengajuanGrade::where('nrp', 'OPD1')->count());
    }

    public function test_tambah_data_searches_monitoring_grade_and_stores_for_selected_year(): void
    {
        $this->monitoringRow(['nama' => 'Zzqx Tester', 'sub_area' => 'SITE-B']);
        $admin = $this->admin();

        $this->actingAs($admin)->getJson('/one-academy/opd/pengajuan-grade/search-employee?q=Zzqx')
            ->assertOk()->assertJsonPath('data.0.nrp', 'OPD1')->assertJsonPath('data.0.name', 'Zzqx Tester');

        $this->actingAs($admin)->post('/one-academy/opd/pengajuan-grade', ['nrp' => 'OPD1', 'tahun' => 2025, 'termin' => 3])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('opd_pengajuan_grade', ['nrp' => 'OPD1', 'tahun' => 2025, 'termin' => 3, 'site' => 'SITE-B']);
    }

    public function test_pengajuan_field_can_be_updated_but_remark_cannot(): void
    {
        $row = OpdPengajuanGrade::create(['nrp' => 'OPD2', 'tahun' => (int) date('Y'), 'termin' => 1, 'nama' => 'Siti']);
        $admin = $this->admin();

        $this->actingAs($admin)->patch("/one-academy/opd/pengajuan-grade/{$row->id}", ['field' => 'grade_tujuan', 'value' => '1D'])->assertRedirect();
        $this->assertSame('1D', $row->fresh()->grade_tujuan);

        $this->actingAs($admin)->patch("/one-academy/opd/pengajuan-grade/{$row->id}", ['field' => 'remark', 'value' => 'x'])->assertSessionHasErrors('field');
    }

    public function test_only_tujuan_mor_and_sp_are_manually_editable(): void
    {
        $row = OpdPengajuanGrade::create(['nrp' => 'OPD3', 'tahun' => (int) date('Y'), 'termin' => 1, 'nama' => 'Ani']);
        $admin = $this->admin();

        foreach (['pos_tujuan', 'grade_tujuan', 'mor', 'sp'] as $field) {
            $this->actingAs($admin)->patch("/one-academy/opd/pengajuan-grade/{$row->id}", ['field' => $field, 'value' => 'X'])->assertSessionHasNoErrors();
        }
        foreach (['site', 'pos_saat_ini', 'grade_saat_ini', 'atr', 'remark'] as $field) {
            $this->actingAs($admin)->patch("/one-academy/opd/pengajuan-grade/{$row->id}", ['field' => $field, 'value' => 'X'])->assertSessionHasErrors('field');
        }
    }

    public function test_current_year_rows_follow_latest_monitoring_grade_data(): void
    {
        $this->monitoringRow(['nrp' => 'OPD4', 'nama' => 'Dedi', 'posisi' => 'OPERATOR DT 30T', 'grade_now' => '2A']);
        OpdPengajuanGrade::create(['nrp' => 'OPD4', 'tahun' => (int) date('Y'), 'termin' => 1, 'nama' => 'Dedi', 'pos_saat_ini' => 'POSISI LAMA', 'grade_saat_ini' => '1A', 'pos_tujuan' => 'TUJUAN MANUAL']);

        $this->actingAs($this->admin())->get('/one-academy/opd/pengajuan-grade')
            ->assertInertia(fn (Assert $page) => $page
                ->where('rows', fn ($rows) => collect($rows)->contains(fn ($r) => $r['nrp'] === 'OPD4'
                    && $r['pos_saat_ini'] === 'OPERATOR DT 30T' && $r['grade_saat_ini'] === '2A' && $r['pos_tujuan'] === 'TUJUAN MANUAL'))
            );
    }

    public function test_ho_api_requires_token_and_sets_remark(): void
    {
        config(['services.pengajuan_api.token' => 'secret-token']);
        OpdPengajuanGrade::create(['nrp' => 'OPD5', 'tahun' => 2026, 'termin' => 2, 'nama' => 'Eka']);

        $this->getJson('/api/pengajuan-grade.php?action=ping')->assertStatus(401);

        $headers = ['X-API-Key' => 'secret-token'];
        $this->getJson('/api/pengajuan-grade.php?action=ping', $headers)->assertOk()->assertJsonPath('success', true);

        $this->postJson('/api/pengajuan-grade.php?action=set_remark', ['nrp' => 'OPD5', 'tahun' => 2026, 'termin' => 2, 'remark' => 'Disetujui', 'warna' => 'hijau', 'by' => 'Budi'], $headers)
            ->assertOk()->assertJsonPath('warna', 'hijau');
        $this->assertDatabaseHas('opd_pengajuan_grade', ['nrp' => 'OPD5', 'remark' => 'Disetujui', 'remark_warna' => 'hijau', 'updated_by' => 'HO:Budi']);

        $this->postJson('/api/pengajuan-grade?action=set_remark', ['nrp' => 'OPD5', 'tahun' => 2026, 'termin' => 2, 'warna' => 'biru'], $headers)->assertStatus(422);
        $this->postJson('/api/pengajuan-grade?action=set_remark', ['nrp' => 'NONE', 'tahun' => 2026, 'termin' => 2], $headers)->assertNotFound();

        $this->getJson('/api/pengajuan-grade.php?action=list&tahun=2026&nrp=OPD5', $headers)
            ->assertOk()->assertJsonPath('count', 1)->assertJsonPath('items.0.remark', 'Disetujui')->assertJsonPath('items.0.remark_warna', 'hijau');
    }

    public function test_minimal_grade_import_from_csv_updates_existing_unit(): void
    {
        OpdMinimalGrade::create(['unit' => 'HD785', 'grade_min_max' => '1A', 'grade_min' => '1A']);
        $csv = UploadedFile::fake()->createWithContent('mg.csv', "UNIT,GRADE MIN-MAX,GRADE MIN,CLUSTER UNIT\nHD785,1E - 2A,1E,\nDT30,1B - 1D,1B,Cluster A\n");

        $this->actingAs($this->admin())->post('/one-academy/opd/master-grade/minimal-grade/import', ['file_sheet' => $csv])
            ->assertSessionHas('success');

        $this->assertSame('1E', OpdMinimalGrade::where('unit', 'HD785')->value('grade_min'));
        $this->assertDatabaseHas('opd_minimal_grade', ['unit' => 'DT30', 'cluster_unit' => 'Cluster A']);
    }

    public function test_minimal_grade_requires_unit(): void
    {
        $this->actingAs($this->admin())->post('/one-academy/opd/master-grade/minimal-grade', ['unit' => ''])
            ->assertSessionHasErrors('unit');
    }
}
