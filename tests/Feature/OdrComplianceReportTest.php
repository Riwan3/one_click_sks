<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\OdrAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class OdrComplianceReportTest extends TestCase
{
    use DatabaseTransactions;

    private const BASE = '/one-reporting/laporan';

    private const RANGE = 'tgl_dari=2031-03-01&tgl_sampai=2031-03-31';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);

        $rows = [
            ['tanggal' => '2031-03-05', 'pengawas' => 'GL Report A', 'status' => 'selesai', 'tasks' => ['P5M', 'APD'], 'done' => [true, true]],
            ['tanggal' => '2031-03-05', 'pengawas' => 'GL Report B', 'status' => 'progress', 'tasks' => ['P5M', 'APD', 'HK'], 'done' => [true, false, false]],
            ['tanggal' => '2031-03-06', 'pengawas' => 'GL Report A', 'status' => 'belum', 'tasks' => ['P5M'], 'done' => [false]],
        ];
        foreach ($rows as $row) {
            OdrAssignment::create([
                'tanggal' => $row['tanggal'], 'shift' => 'Shift 1', 'area' => 'PIT REPORT', 'unit' => 'HD785', 'pengawas' => $row['pengawas'],
                'status' => $row['status'], 'aktivitas' => json_encode($row['tasks']), 'task_done' => json_encode($row['done']),
            ]);
        }
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'trep'.random_int(10000, 99999)]);
    }

    public function test_laporan_tab_filters_by_pengawas_and_status(): void
    {
        $this->actingAs($this->admin())
            ->get(self::BASE.'?tab=laporan&'.self::RANGE.'&area=PIT+REPORT&pengawas=Report+B&status=progress')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('reportRows', 1)
                ->where('reportRows.0.report_pct', 33.3));
    }

    public function test_rekap_harian_ignores_status_filter_and_averages_percent(): void
    {
        $this->actingAs($this->admin())
            ->get(self::BASE.'?tab=rekap_harian&'.self::RANGE.'&area=PIT+REPORT&status=selesai')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('dailyRecap', 2)
                ->where('dailyRecap.1.tanggal', '2031-03-05')
                ->where('dailyRecap.1.total', 2)
                ->where('dailyRecap.1.pct', 66.7));
    }

    public function test_rekap_bulanan_groups_by_day_pengawas_and_area(): void
    {
        $this->actingAs($this->admin())
            ->get(self::BASE.'?tab=rekap_bulanan&bulan=3&tahun=2031')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('monthlyRecap.harian.0.label', '5')
                ->where('monthlyRecap.harian.0.pct', 66.7)
                ->where('monthlyRecap.pengawas.0.label', 'GL Report A')
                ->where('monthlyRecap.area.0.pct', 44.4));
    }

    public function test_export_contains_dynamic_task_columns(): void
    {
        $response = $this->actingAs($this->admin())->get(self::BASE.'/export?'.self::RANGE.'&area=PIT+REPORT')->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString('Task 3', $csv);
        $this->assertStringContainsString('✓ P5M', $csv);
        $this->assertStringContainsString('Belum Dimulai', $csv);
    }

    public function test_operator_cannot_open_report(): void
    {
        $operator = User::factory()->create(['role' => 'operator', 'username' => 'trepop'.random_int(1000, 9999)]);

        $this->actingAs($operator)->get(self::BASE)->assertForbidden();
    }
}
