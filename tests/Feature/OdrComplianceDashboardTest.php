<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\OdrAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class OdrComplianceDashboardTest extends TestCase
{
    use DatabaseTransactions;

    private const DATE = '2031-01-15';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);

        $rows = [
            ['area' => 'PIT ZETA', 'pengawas' => 'GL Alpha', 'status' => 'selesai', 'shift' => 'Shift 1'],
            ['area' => 'PIT ZETA', 'pengawas' => 'GL Alpha', 'status' => 'progress', 'shift' => 'Shift 1'],
            ['area' => 'DISPOSAL ZETA', 'pengawas' => 'GL Beta', 'status' => 'belum', 'shift' => 'Shift 1'],
            ['area' => 'PIT ZETA', 'pengawas' => 'GL Beta', 'status' => 'belum', 'shift' => 'Shift 2'],
        ];
        foreach ($rows as $row) {
            OdrAssignment::create([...$row, 'tanggal' => self::DATE, 'unit' => 'HD785', 'aktivitas' => 'P2H']);
        }
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'username' => 'todr'.$role.random_int(1000, 9999)]);
    }

    public function test_area_filter_only_applies_to_outstanding_like_v1(): void
    {
        $this->actingAs($this->user('group-leader'))
            ->get('/one-reporting/dashboard?tanggal='.self::DATE.'&shift=Shift+1&area=DISPOSAL+ZETA')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('summary.total', 3)
                ->where('summary.selesai', 1)
                ->where('summary.progress', 1)
                ->where('summary.belum', 1)
                ->where('summary.persentase', 33.3)
                ->has('outstanding', 1)
                ->where('outstanding.0.area', 'DISPOSAL ZETA')
                ->where('topPengawas.0.label', 'GL Alpha')
                ->where('topPengawas.0.persen', 50)
                ->where('lowestAreas.0.label', 'DISPOSAL ZETA')
                ->has('dailyTrend', 7));
    }

    public function test_operator_cannot_open_compliance_dashboard(): void
    {
        $this->actingAs($this->user('operator'))->get('/one-reporting/dashboard')->assertForbidden();
    }
}
