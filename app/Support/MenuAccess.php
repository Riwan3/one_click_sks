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
    ];

    /**
     * Ambil menu sidebar dari config, difilter sesuai akses role user yang login
     * (diatur admin di One-Core > Role & Akses). Guest/tanpa login = menu utuh.
     */
    public static function forConfig(string $configKey): array
    {
        $menuTree = config($configKey, []);

        $user = Auth::user();
        $moduleKey = self::CONFIG_TO_MODULE_KEY[$configKey] ?? null;

        if (! $user || ! $moduleKey) {
            return $menuTree;
        }

        return RoleMenuAccess::filterMenuTree($menuTree, $user->role, $moduleKey);
    }
}
