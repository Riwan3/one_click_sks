<?php

namespace Tests\Feature\OneSetting;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\FleetAllocation;
use App\Models\SettingBoardView;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SettingBoardViewTest extends TestCase
{
    use DatabaseTransactions;

    private const DATE = '2031-07-07';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'username' => 'tmp'.random_int(10000, 99999)]);
    }

    private function seedBoards(): void
    {
        DB::table('v2_mp_settings')->insert(['work_date' => self::DATE, 'shift' => 'D']);
        FleetAllocation::create(['tanggal' => self::DATE, 'shift' => 1, 'group_name' => 'ZT OB', 'loader_id' => 'ZT-E1', 'unit_nrp' => null]);
    }

    private function openBoard(User $user): void
    {
        $this->actingAs($user)->get('/one-setting/setting-board?date='.self::DATE.'&shift=D')->assertOk();
    }

    public function test_non_admin_opening_board_with_data_is_recorded_once_per_window_and_admin_is_not(): void
    {
        $admin = $this->user('admin');
        $operator = $this->user('operator');
        $this->seedBoards();

        $this->openBoard($admin);
        $this->assertSame(0, SettingBoardView::where('user_id', $admin->id)->count());

        $this->openBoard($operator);
        $this->openBoard($operator);

        $views = SettingBoardView::where('user_id', $operator->id)->get();
        $this->assertEqualsCanonicalizing(['manpower', 'fleet'], $views->pluck('board_type')->all());
        $this->assertSame([self::DATE, self::DATE], $views->map(fn ($view) => $view->work_date->format('Y-m-d'))->all());

        SettingBoardView::where('user_id', $operator->id)->update(['viewed_at' => now()->subMinutes(30)]);
        $this->openBoard($operator);
        $this->assertSame(4, SettingBoardView::where('user_id', $operator->id)->count());
    }

    public function test_board_without_data_is_not_recorded(): void
    {
        $operator = $this->user('operator');

        $this->actingAs($operator)->get('/one-setting/setting-board?date=2031-08-08&shift=D')->assertOk();

        $this->assertSame(0, SettingBoardView::where('user_id', $operator->id)->count());
    }

    public function test_dashboard_view_stats_summarise_month_for_non_admin_only(): void
    {
        $admin = $this->user('admin');
        $operator = $this->user('operator');
        $other = $this->user('operator');
        $row = fn (User $user, string $type, string $at) => SettingBoardView::create([
            'user_id' => $user->id, 'board_type' => $type, 'work_date' => self::DATE, 'shift' => 'D', 'viewed_at' => $at,
        ]);
        $row($operator, 'manpower', '2031-09-02 08:00:00');
        $row($operator, 'fleet', '2031-09-02 08:01:00');
        $row($other, 'manpower', '2031-09-15 21:00:00');
        $row($other, 'manpower', '2031-10-01 01:00:00');

        $this->actingAs($admin)->get('/one-setting/dashboard?month=2031-09')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('month', '2031-09')
                ->where('viewStats.totals', ['manpower' => 2, 'fleet' => 1, 'users' => 2])
                ->has('viewStats.days', 30)
                ->where('viewStats.days.1', ['date' => '2031-09-02', 'manpower' => 1, 'fleet' => 1])
                ->where('viewStats.days.14.manpower', 1));

        $this->actingAs($admin)->get('/one-setting/dashboard?month=bukan-bulan')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('month', date('Y-m')));
    }
}
