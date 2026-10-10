<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminCommand extends Command
{
    protected $signature = 'auth:create-admin
        {username : NRP / username login}
        {--name= : Nama tampilan (default: nama karyawan dengan NRP sama, atau username)}';

    protected $description = 'Buat atau perbarui akun admin (password diketik tersembunyi), mis. untuk akun pertama di server baru';

    private const MIN_PASSWORD_LENGTH = 8;

    public function handle(): int
    {
        $username = trim((string) $this->argument('username'));
        if ($username === '') {
            $this->error('Username/NRP tidak boleh kosong.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password (min. '.self::MIN_PASSWORD_LENGTH.' karakter)');
        if (mb_strlen($password) < self::MIN_PASSWORD_LENGTH || $password === $username) {
            $this->error('Password minimal '.self::MIN_PASSWORD_LENGTH.' karakter dan tidak boleh sama dengan username.');

            return self::FAILURE;
        }
        if ($password !== (string) $this->secret('Ulangi password')) {
            $this->error('Konfirmasi password tidak sama.');

            return self::FAILURE;
        }

        $employee = Employee::query()->where('nrp', $username)->first();
        $user = User::query()->updateOrCreate(['username' => $username], [
            'employee_id' => $employee?->id,
            'name' => $this->option('name') ?: ($employee?->name ?? $username),
            'role' => 'admin',
            'password' => Hash::make($password),
        ]);

        $this->info(($user->wasRecentlyCreated ? 'Akun admin dibuat' : 'Akun admin diperbarui').": {$user->username}");

        return self::SUCCESS;
    }
}
