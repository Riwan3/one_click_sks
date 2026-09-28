<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\OdrIncident;
use App\Models\OdrIncidentEvidence;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class OdrIncidentTest extends TestCase
{
    use DatabaseTransactions;

    private const BASE = '/one-reporting/perbaikan-pasca-insiden';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
        Storage::fake('public');
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'tinc'.random_int(10000, 99999)]);
    }

    private function makeIncident(array $attributes = []): OdrIncident
    {
        return OdrIncident::create(['tanggal_insiden' => '2031-05-01', 'judul_insiden' => 'Insiden Uji', 'status' => 'open', ...$attributes]);
    }

    public function test_admin_can_create_incident_and_update_status(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(self::BASE, ['tanggal_insiden' => '2031-05-01', 'judul_insiden' => 'Unit Terguling Uji', 'area' => 'MFOB'])
            ->assertSessionHas('success', 'Data insiden "Unit Terguling Uji" berhasil ditambahkan.');

        $incident = OdrIncident::where('judul_insiden', 'Unit Terguling Uji')->firstOrFail();
        $this->assertSame('open', $incident->status->value);

        $this->actingAs($admin)->put(self::BASE."/{$incident->id}/status", ['status' => 'progress', 'target_selesai' => '2031-05-10'])->assertSessionHasNoErrors();
        $this->assertSame('2031-05-10', $incident->fresh()->target_selesai->toDateString());
    }

    public function test_incident_requires_date_and_title(): void
    {
        $this->actingAs($this->admin())->post(self::BASE, [])->assertSessionHasErrors(['judul_insiden' => 'Tanggal insiden dan judul wajib diisi.']);
    }

    public function test_actions_are_numbered_continuously_with_evidence_and_kpi_counts_overdue(): void
    {
        $incident = $this->makeIncident(['target_selesai' => now()->subDay()->toDateString()]);
        $admin = $this->admin();

        $this->actingAs($admin)->post(self::BASE."/{$incident->id}/tindakan", [
            'jenis' => 'pencegahan', 'uraian_tindakan' => 'Pasang rambu', 'pic' => 'GL A',
            'evidence' => [UploadedFile::fake()->image('foto.jpg'), UploadedFile::fake()->create('script.exe', 10)],
        ])->assertSessionHas('success', 'Tindakan berhasil ditambahkan.');
        $this->actingAs($admin)->post(self::BASE."/{$incident->id}/tindakan", ['jenis' => 'mitigasi', 'uraian_tindakan' => 'Evakuasi unit']);

        $this->assertSame(1, OdrIncidentEvidence::count());

        $this->actingAs($admin)->get(self::BASE)
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('kpi.overdue', OdrIncident::overdue()->count())
                ->where('incidents', fn ($rows) => collect($rows)->contains(fn ($r) => $r['id'] === $incident->id
                    && $r['is_overdue'] === true
                    && count($r['pencegahan']) === 1
                    && $r['pencegahan'][0]['evidence'][0]['is_image'] === true
                    && count($r['mitigasi']) === 1)));
    }

    public function test_deleting_incident_removes_actions_and_evidence_files(): void
    {
        $incident = $this->makeIncident();
        $admin = $this->admin();
        $this->actingAs($admin)->post(self::BASE."/{$incident->id}/tindakan", ['jenis' => 'pencegahan', 'uraian_tindakan' => 'X', 'evidence' => [UploadedFile::fake()->create('bukti.pdf', 5)]]);
        $path = OdrIncidentEvidence::firstOrFail()->file_path;
        Storage::disk('public')->assertExists($path);

        $this->actingAs($admin)->delete(self::BASE."/{$incident->id}")->assertSessionHas('success', 'Data insiden berhasil dihapus.');

        $this->assertModelMissing($incident);
        $this->assertSame(0, OdrIncidentEvidence::count());
        Storage::disk('public')->assertMissing($path);
    }

    public function test_operator_is_forbidden(): void
    {
        $operator = User::factory()->create(['role' => 'operator', 'username' => 'tincop'.random_int(1000, 9999)]);

        $this->actingAs($operator)->get(self::BASE)->assertForbidden();
    }
}
