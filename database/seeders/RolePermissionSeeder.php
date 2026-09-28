<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Nilai awal is_mobile — data seed, bukan aturan tetap. Bisa diubah admin
     * kapan saja lewat One-Core > Role & Akses; seeder tidak menimpa baris
     * yang sudah ada supaya perubahan admin tidak tertindih saat re-seed.
     */
    private const INITIAL_MOBILE_ROLES = [
        'group-leader',
        'helper',
        'trafficman',
        'tamu',
        'operator',
        'medic',
        'security',
    ];

    public function run(): void
    {
        foreach (Role::all() as $role) {
            RolePermission::query()->firstOrCreate(
                ['role' => $role->code],
                ['is_mobile' => in_array($role->code, self::INITIAL_MOBILE_ROLES, true)]
            );
        }

        // Akun Group Leader di GMP default ke GMP Area (Dashboard GMP dinonaktifkan)
        \App\Models\RoleMenuAccess::query()->firstOrCreate(
            ['role' => 'group-leader', 'module_key' => 'one-gmp', 'menu_key' => 'dashboard-gmp'],
            ['enabled' => false]
        );
    }
}
