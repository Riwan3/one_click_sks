<?php

namespace Tests\Feature\OneSetting;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SettingBoardManpowerTest extends TestCase
{
    use DatabaseTransactions;

    private const DATE = '2026-10-05';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'tmp'.random_int(10000, 99999)]);
    }

    public function test_manpower_board_uses_setting_manpower_preview_for_same_date_and_shift(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/one-setting/manpower/settings', ['date' => self::DATE, 'shift' => 'D'])->assertSessionHas('success');

        $this->actingAs($admin)->get('/one-setting/setting-board?date='.self::DATE.'&shift=D')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('OneSetting/SettingBoard')
                ->where('manpowerPreview.view.setting.work_date', self::DATE)
                ->where('manpowerPreview.view.setting.shift', 'D')
                ->has('manpowerPreview.view.tree'));
    }

    public function test_manpower_board_is_empty_when_setting_not_created_or_input_invalid(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/one-setting/setting-board?date=2031-01-01&shift=D')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('manpowerPreview', null));

        $this->actingAs($admin)->get('/one-setting/setting-board?date=bukan-tanggal&shift=X')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('manpowerPreview', null));
    }
}
