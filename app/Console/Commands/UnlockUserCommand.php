<?php

namespace App\Console\Commands;

use App\Http\Controllers\AuthController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\RateLimiter;

class UnlockUserCommand extends Command
{
    protected $signature = 'auth:unlock {username : NRP / Username pengguna yang ingin di-unlock}';

    protected $description = 'Buka kunci akun (clear rate limiter) yang terkena lockout akibat salah password berulang';

    public function handle(): int
    {
        $username = trim((string) $this->argument('username'));

        if (! $username) {
            $this->error('Username/NRP tidak boleh kosong.');
            return self::FAILURE;
        }

        $throttleKey = AuthController::getThrottleKey($username);

        RateLimiter::clear($throttleKey);

        $this->info("✓ Akun [{$username}] berhasil di-unlock!");
        $this->line("Pengguna sekarang dapat langsung melakukan login kembali tanpa menunggu cooldown.");

        return self::SUCCESS;
    }
}
