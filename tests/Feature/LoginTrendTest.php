<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class LoginTrendTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
    }

    private function log(string $nrp, string $at, string $status = 'SUCCESS'): void
    {
        LoginLog::create(['username' => $nrp, 'nama' => 'Tes '.$nrp, 'status' => $status, 'ip_address' => '10.0.0.1', 'login_at' => $at]);
    }

    public function test_master_sees_weekly_shift_series_unique_users_and_never_logged_in(): void
    {
        $user = User::factory()->create(['role' => 'master', 'username' => 'tmpmaster'.random_int(1000, 9999)]);
        $this->log('9990001', '2031-03-02 08:00:00');
        $this->log('9990001', '2031-03-03 20:00:00');
        $this->log('9990002', '2031-03-09 06:00:00');
        $this->log('9990002', '2031-03-10 18:45:00');
        $this->log('9990003', '2031-03-11 09:00:00', 'FAILED_PASSWORD');
        $this->log('9990004', '2031-04-01 09:00:00');

        $this->actingAs($user)->get('/login-trend?bulan=3&tahun=2031')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('LoginTrend/Index')
                ->where('report.totals', ['total' => 4, 'shift1' => 1, 'shift2' => 2, 'lainnya' => 1])
                ->where('report.series.Minggu 1 (1-7)', ['shift1' => 1, 'shift2' => 1, 'lainnya' => 0])
                ->where('report.series.Minggu 2 (8-14)', ['shift1' => 0, 'shift2' => 1, 'lainnya' => 1])
                ->has('report.series', 5)
                ->where('report.uniqueUserCount', 2)
                ->where('report.uniqueUsers.0.login_count', 2)
                ->has('never.rows')
                ->where('filters.bulan', 3));
    }

    public function test_user_without_access_gets_403_and_menu_flag_is_false(): void
    {
        $operator = User::factory()->create(['role' => 'operator', 'username' => 'tmpop'.random_int(1000, 9999)]);

        $this->actingAs($operator)->get('/login-trend')->assertForbidden();
        $this->assertFalse($operator->canViewLoginTrend());
        $this->assertTrue(User::factory()->make(['role' => 'admin', 'username' => '230256'])->canViewLoginTrend());
    }

    public function test_invalid_month_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'master', 'username' => 'tmpmaster'.random_int(1000, 9999)]);

        $this->actingAs($user)->get('/login-trend?bulan=13')->assertSessionHasErrors('bulan');
    }
}
