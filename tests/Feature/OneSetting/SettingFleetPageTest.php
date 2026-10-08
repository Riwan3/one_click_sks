<?php

namespace Tests\Feature\OneSetting;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SettingFleetPageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_setting_fleet_page_renders_with_menu_entry(): void
    {
        $this->withoutMiddleware(CheckSessionSecurity::class);
        $user = User::factory()->create(['role' => 'admin', 'username' => 'tmp'.random_int(10000, 99999)]);

        $this->actingAs($user)->get('/one-setting/fleet')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('OneSetting/Fleet/Index')
                ->where('menu', fn ($menu) => collect($menu)->contains('label', 'Setting Fleet')));
    }

    public function test_fleet_preview_page_renders_sheet_or_empty_state(): void
    {
        $this->withoutMiddleware(CheckSessionSecurity::class);
        $user = User::factory()->create(['role' => 'admin', 'username' => 'tmp'.random_int(10000, 99999)]);

        $this->actingAs($user)->get('/one-setting/fleet/preview?date=2031-01-01&shift=D')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('OneSetting/Fleet/Preview')
                ->where('filters.date', '2031-01-01')
                ->where('sheet', null));

        $operator = User::factory()->create(['role' => 'operator', 'username' => 'tmpop'.random_int(10000, 99999)]);
        $this->actingAs($operator)->get('/one-setting/fleet/preview')->assertForbidden();
    }
}
