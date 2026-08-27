<?php

namespace Database\Seeders;

use App\Enums\RoleScope;
use App\Enums\UserRole;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Nilai awal landing_path — data seed, bukan aturan tetap. Bisa diubah admin
     * kapan saja lewat One-Core > Role & Akses. Role tanpa landing_path memakai
     * alur default (RolePermission.is_mobile menentukan Home Portal vs mobile home).
     */
    private const INITIAL_LANDING_PATHS = [
        'medic' => '/one-power/deviasi-operator/izin-karyawan',
    ];

    /**
     * Role dengan akses lintas fungsi/One-Core (Control Center Role). Role lain
     * di luar daftar ini adalah Site Role — role operasional yang berlaku untuk
     * site ini saja (lihat migration add_scope_to_roles_table).
     */
    private const CONTROL_CENTER_ROLES = ['admin', 'management'];

    /**
     * Baseline 10 role dari UserRole enum. is_system = true supaya tidak bisa
     * dihapus dari UI — role ini masih dipakai logic bisnis (fromJabatan, dst).
     */
    public function run(): void
    {
        foreach (UserRole::cases() as $role) {
            Role::query()->firstOrCreate(
                ['code' => $role->value],
                [
                    'label' => $role->label(),
                    'scope' => in_array($role->value, self::CONTROL_CENTER_ROLES, true)
                        ? RoleScope::ControlCenter
                        : RoleScope::Site,
                    'landing_path' => self::INITIAL_LANDING_PATHS[$role->value] ?? null,
                    'is_system' => true,
                ]
            );
        }
    }
}
