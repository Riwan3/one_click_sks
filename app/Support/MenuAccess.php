<?php

namespace App\Support;

use App\Models\RoleMenuAccess;
use Illuminate\Support\Facades\Auth;

class MenuAccess
{
    /**
     * config/one_<x>_menu.php key => module_key sesuai config/modules.php.
     */
    private const CONFIG_TO_MODULE_KEY = [
        'one_power_menu' => 'one-power',
        'one_academy_menu' => 'one-academy',
        'one_core_menu' => 'one-core',
        'one_dms_menu' => 'one-dms',
        'one_gmp_menu' => 'one-gmp',
        'one_reporting_menu' => 'one-reporting',
        'one_setting_menu' => 'one-setting',
        'one_procedure_menu' => 'one-procedure',
        'one_roster_menu' => 'one-roster',
    ];

    /**
     * Ambil menu sidebar dari config, difilter sesuai akses role user yang login
     * (diatur admin di One-Core > Role & Akses). Guest/tanpa login = menu utuh.
     */
    public static function forConfig(string $configKey): array
    {
        $menu = self::filteredForRole($configKey);
        $moduleKey = self::CONFIG_TO_MODULE_KEY[$configKey] ?? null;

        // Lapisan terakhir: menu yang dinonaktifkan dari Control Center (bila diaktifkan di .env).
        return $moduleKey ? ControlCenterPolicy::filterMenus($moduleKey, $menu) : $menu;
    }

    private static function filteredForRole(string $configKey): array
    {
        $menuTree = config($configKey, []);

        $user = Auth::user();
        $moduleKey = self::CONFIG_TO_MODULE_KEY[$configKey] ?? null;

        if (! $user || ! $moduleKey) {
            return $menuTree;
        }

        $filtered = RoleMenuAccess::filterMenuTree($menuTree, $user->role, $moduleKey);

        // Hanya Admin, Software Engineer, Section Head, & Dept Head yang bisa melihat menu Roster Departemen
        if ($configKey === 'one_power_menu' && method_exists($user, 'canViewAllRosters') && ! $user->canViewAllRosters()) {
            foreach ($filtered as &$item) {
                if (($item['menu_key'] ?? '') === 'roster' && isset($item['children'])) {
                    $item['children'] = array_values(array_filter($item['children'], function ($child) {
                        return ($child['menu_key'] ?? '') !== 'roster-departemen';
                    }));
                }
            }
            unset($item);
        }

        // Setting GL One-Reporting hanya untuk Admin & Admin Produksi (sama seperti v1)
        if ($configKey === 'one_reporting_menu' && ! $user->canManageGlSetting()) {
            foreach ($filtered as &$item) {
                if (($item['menu_key'] ?? '') === 'compliance' && isset($item['children'])) {
                    $item['children'] = array_values(array_filter($item['children'], fn ($child) => ($child['menu_key'] ?? '') !== 'setting-gl'));
                }
            }
            unset($item);
        }

        return $filtered;
    }
}
