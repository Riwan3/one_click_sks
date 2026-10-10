<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Data master untuk server produksi (tanpa karyawan/user dummy).
 * Jalankan: php artisan db:seed --class=ProductionSeeder --force
 * Akun admin pertama dibuat lewat: php artisan auth:create-admin {nrp}
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            RolePermissionSeeder::class,
            MasterSpdkSeeder::class,
            MppUnitSeeder::class,
        ]);
    }
}
