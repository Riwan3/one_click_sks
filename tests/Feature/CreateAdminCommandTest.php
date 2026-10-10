<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_creates_admin_with_hidden_password(): void
    {
        $this->artisan('auth:create-admin', ['username' => 'zt-admin-1', '--name' => 'Admin Server'])
            ->expectsQuestion('Password (min. 8 karakter)', 'rahasia-kuat-123')
            ->expectsQuestion('Ulangi password', 'rahasia-kuat-123')
            ->assertSuccessful();

        $user = User::where('username', 'zt-admin-1')->firstOrFail();
        $this->assertSame(['admin', 'Admin Server'], [$user->role, $user->name]);
        $this->assertTrue(Hash::check('rahasia-kuat-123', $user->password));
    }

    public function test_rejects_weak_or_mismatched_password(): void
    {
        $this->artisan('auth:create-admin', ['username' => 'zt-admin-2'])
            ->expectsQuestion('Password (min. 8 karakter)', 'pendek')
            ->assertFailed();

        $this->artisan('auth:create-admin', ['username' => 'zt-admin-2'])
            ->expectsQuestion('Password (min. 8 karakter)', 'rahasia-kuat-123')
            ->expectsQuestion('Ulangi password', 'beda-password-123')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['username' => 'zt-admin-2']);
    }
}
