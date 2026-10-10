<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Hanya data master (tanpa karyawan/user dummy). Akun admin dibuat lewat: php artisan auth:create-admin {nrp}
     */
    public function run(): void
    {
        $this->call(ProductionSeeder::class);
    }
}
