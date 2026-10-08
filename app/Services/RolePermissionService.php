<?php

namespace App\Services;

use App\Models\Role;
use App\Models\RoleMenuAccess;
use App\Models\RoleModuleAccess;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Support\Collection;

class RolePermissionService
{
    /**
     * module_key => nama config menu (config/one_<module>_menu.php).
     * Modul di luar daftar ini belum punya file menu sendiri.
     */
    private const MODULE_MENU_CONFIG = [
        'one-power' => 'one_power_menu',
        'one-academy' => 'one_academy_menu',
        'one-core' => 'one_core_menu',
        'one-dms' => 'one_dms_menu',
        'one-gmp' => 'one_gmp_menu',
        'one-reporting' => 'one_reporting_menu',
        'one-setting' => 'one_setting_menu',
        'one-procedure' => 'one_procedure_menu',
        'one-roster' => 'one_roster_menu',
    ];

    public function summaryForAllRoles(): array
    {
        $modules = collect(config('modules.list'));

        return Role::query()->orderBy('label')->get()->map(function (Role $role) use ($modules) {
            $allowedKeys = RoleModuleAccess::allowedModuleKeys($role->code);

            return [
                'role' => $role->code,
                'label' => $role->label,
                'scope' => $role->scope->value,
                'isSystem' => $role->is_system,
                'isMobile' => RolePermission::isMobile($role->code),
                'modules' => $modules->map(fn (array $module) => [
                    'key' => $module['key'],
                    'title' => $module['title'],
                    'enabled' => $allowedKeys->contains($module['key']),
                ])->values(),
            ];
        })->values()->all();
    }

    public function update(string $role, bool $isMobile, array $enabledModuleKeys): void
    {
        RolePermission::query()->updateOrCreate(
            ['role' => $role],
            ['is_mobile' => $isMobile]
        );

        foreach (collect(config('modules.list'))->pluck('key') as $moduleKey) {
            RoleModuleAccess::query()->updateOrCreate(
                ['role' => $role, 'module_key' => $moduleKey],
                ['enabled' => in_array($moduleKey, $enabledModuleKeys, true)]
            );
        }
    }

    /**
     * Detail menu per module untuk 1 role — dipakai halaman Role & Akses > Detail.
     * Hanya module yang enabled untuk role ini (RoleModuleAccess) dan punya file
     * config menu yang ditampilkan — konsisten dengan checklist module di atasnya.
     */
    public function menuDetailForRole(string $role): array
    {
        $allowedModuleKeys = RoleModuleAccess::allowedModuleKeys($role);

        $modules = collect(config('modules.list'))->filter(
            fn (array $module) => array_key_exists($module['key'], self::MODULE_MENU_CONFIG)
                && $allowedModuleKeys->contains($module['key'])
        );

        return $modules->map(function (array $module) use ($role) {
            $disabled = RoleMenuAccess::disabledMenuKeys($role, $module['key']);

            return [
                'key' => $module['key'],
                'title' => $module['title'],
                'menus' => $this->flattenMenuTree(config(self::MODULE_MENU_CONFIG[$module['key']]))
                    ->map(fn (array $menu) => [...$menu, 'enabled' => ! $disabled->contains($menu['menuKey'])])
                    ->values(),
            ];
        })->values()->all();
    }

    public function updateMenuAccess(string $role, string $moduleKey, array $enabledMenuKeys): void
    {
        if (! array_key_exists($moduleKey, self::MODULE_MENU_CONFIG)) {
            abort(404);
        }

        $allMenuKeys = $this->flattenMenuTree(config(self::MODULE_MENU_CONFIG[$moduleKey]))->pluck('menuKey');

        foreach ($allMenuKeys as $menuKey) {
            RoleMenuAccess::query()->updateOrCreate(
                ['role' => $role, 'module_key' => $moduleKey, 'menu_key' => $menuKey],
                ['enabled' => in_array($menuKey, $enabledMenuKeys, true)]
            );
        }
    }

    public function createRole(string $code, string $label, string $scope): Role
    {
        return Role::create(['code' => $code, 'label' => $label, 'scope' => $scope, 'is_system' => false]);
    }

    public function deleteRole(Role $role): void
    {
        if ($role->is_system) {
            abort(422, 'Role bawaan sistem tidak bisa dihapus.');
        }

        if (User::query()->where('role', $role->code)->exists()) {
            abort(422, 'Role masih dipakai oleh user aktif, pindahkan usernya dulu sebelum menghapus role ini.');
        }

        RolePermission::query()->where('role', $role->code)->delete();
        RoleModuleAccess::query()->where('role', $role->code)->delete();
        RoleMenuAccess::query()->where('role', $role->code)->delete();
        $role->delete();
    }

    /**
     * Ratakan tree menu jadi list flat dengan info kedalaman (depth) untuk
     * ditampilkan sebagai checklist indented, tanpa menu induk yang punya children.
     */
    private function flattenMenuTree(array $items, int $depth = 0): Collection
    {
        $result = collect();

        foreach ($items as $item) {
            if (isset($item['children'])) {
                $result = $result->merge($this->flattenMenuTree($item['children'], $depth + 1));

                continue;
            }

            $result->push([
                'menuKey' => $item['menu_key'],
                'label' => $item['label'],
                'depth' => $depth,
            ]);
        }

        return $result;
    }
}
