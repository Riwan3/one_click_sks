<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\OdrAssignment;
use App\Models\OdrGlSetting;
use App\Models\User;
use App\Support\OdrGlAllocationParser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OdrGlSettingTest extends TestCase
{
    use DatabaseTransactions;

    private const BASE = '/one-reporting/setting-gl';

    private const DATE = '2031-04-01';

    private const FRONT_TEXT = "JL.JERMAN ARAH SPAYOL\nE 2046  Irfan\n_E 3904  Taufik_\n\nIPPKH Atas\nE 2042  Agung";

    private const DISPOSAL_TEXT = "Disposal Barat\n*Pic : Budi*\nFleet :\n- E5001\n- E5002\n\nDisposal Monte RL +120\tArif";

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'username' => 'tgl'.substr(md5($role), 0, 5).random_int(1000, 9999)]);
    }

    public function test_parser_reads_whatsapp_and_tab_formats(): void
    {
        $parser = new OdrGlAllocationParser;

        $this->assertSame([
            ['area' => 'JL.JERMAN ARAH SPAYOL', 'unit' => 'E2046', 'pic' => 'Irfan'],
            ['area' => 'JL.JERMAN ARAH SPAYOL', 'unit' => 'E3904', 'pic' => 'Taufik'],
            ['area' => 'IPPKH Atas', 'unit' => 'E2042', 'pic' => 'Agung'],
        ], $parser->front(self::FRONT_TEXT));

        $this->assertSame([
            ['area' => 'Disposal Barat', 'unit' => 'E5001', 'pic' => 'Budi'],
            ['area' => 'Disposal Barat', 'unit' => 'E5002', 'pic' => 'Budi'],
            ['area' => 'Disposal Monte RL +120', 'unit' => '', 'pic' => 'Arif'],
        ], $parser->disposal(self::DISPOSAL_TEXT));
    }

    public function test_parse_saves_mapping_and_syncs_assignments_without_touching_manual_ones(): void
    {
        $manual = OdrAssignment::create(['tanggal' => self::DATE, 'shift' => 'Shift 1', 'area' => 'MFOB', 'unit' => 'X', 'pengawas' => 'Manual', 'status' => 'belum']);
        $admin = $this->user('admin');
        $payload = ['tanggal' => self::DATE, 'shift' => 'Shift 1', 'front_text' => self::FRONT_TEXT, 'disposal_text' => self::DISPOSAL_TEXT];

        $this->actingAs($admin)->post(self::BASE.'/parse', $payload)
            ->assertSessionHas('success', '6 baris disimpan untuk 01/04/2031 - Shift 1 — Front PIC: 3, Disposal PIC: 3. 5 assignment disinkronkan ke Daily Assignment.');

        // Parse ulang tidak menggandakan data
        $this->actingAs($admin)->post(self::BASE.'/parse', $payload);

        $this->assertSame(6, OdrGlSetting::whereDate('tanggal', self::DATE)->count());
        $synced = OdrAssignment::whereDate('tanggal', self::DATE)->where('gl_synced', true)->get();
        $this->assertCount(5, $synced);
        $this->assertSame(['GMP', 'Inspeksi Area'], $synced->first()->tasks());
        $this->assertSame(['Front Loading', 'Disposal'], $synced->pluck('area')->unique()->values()->all());
        $this->assertModelExists($manual);

        $this->actingAs($admin)->delete(self::BASE.'/batch', ['tanggal' => self::DATE, 'shift' => 'Shift 1'])->assertSessionHas('success');
        $this->assertSame(0, OdrAssignment::whereDate('tanggal', self::DATE)->where('gl_synced', true)->count());
        $this->assertModelExists($manual);
    }

    public function test_unparseable_text_returns_error(): void
    {
        $this->actingAs($this->user('admin'))->post(self::BASE.'/parse', ['tanggal' => self::DATE, 'shift' => 'Shift 1', 'front_text' => "\t\t"])
            ->assertSessionHas('error');
    }

    public function test_only_admin_can_access_and_menu_is_hidden_for_others(): void
    {
        $gl = $this->user('group-leader');

        $this->actingAs($gl)->get(self::BASE)->assertForbidden();
        $this->actingAs($gl)->get('/one-reporting/daily')
            ->assertInertia(fn ($page) => $page->where('menu', fn ($menu) => $this->complianceChildKeys($menu) === ['daily-compliance', 'laporan-compliance', 'master-compliance']));
        $this->actingAs($this->user('admin'))->get(self::BASE)->assertOk()
            ->assertInertia(fn ($page) => $page->where('menu', fn ($menu) => in_array('setting-gl', $this->complianceChildKeys($menu), true)));
    }

    private function complianceChildKeys($menu): array
    {
        return collect(collect($menu)->firstWhere('menu_key', 'compliance')['children'] ?? [])->pluck('menu_key')->all();
    }
}
