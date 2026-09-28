<?php

namespace Tests\Feature;

use App\Models\SettingBoard;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SettingBoardControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->create(['username' => 'tsb'.random_int(10000, 99999), 'role' => 'admin']);
    }

    public function test_index_shows_null_boards_when_none_uploaded(): void
    {
        $this->actingAs($this->admin())
            ->get('/one-setting/setting-board?site=ZZZ&date=2030-01-01&shift=D')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OneSetting/SettingBoard')
                ->where('settingBoards.manpower', null)
                ->where('settingBoards.fleet', null)
            );
    }

    public function test_upload_then_delete_board_image(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/one-setting/boards/upload', [
                'site' => 'ZZZ',
                'tanggal' => '2030-01-01',
                'shift' => 'D',
                'type' => 'manpower',
                'image' => UploadedFile::fake()->image('board.jpg'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $board = SettingBoard::where('site', 'ZZZ')->where('type', 'manpower')->first();
        $this->assertNotNull($board);
        Storage::disk('public')->assertExists($board->image_path);

        $this->actingAs($admin)
            ->get('/one-setting/setting-board?site=ZZZ&date=2030-01-01&shift=D')
            ->assertInertia(fn (Assert $page) => $page
                ->where('settingBoards.manpower.site', 'ZZZ')
                ->where('settingBoards.fleet', null)
            );

        $this->actingAs($admin)
            ->delete("/one-setting/boards/delete/{$board->id}")
            ->assertSessionHasNoErrors();

        $this->assertNull(SettingBoard::find($board->id));
        Storage::disk('public')->assertMissing($board->image_path);
    }
}
