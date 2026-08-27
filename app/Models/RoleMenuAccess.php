<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RoleMenuAccess extends Model
{
    protected $fillable = [
        'role',
        'module_key',
        'menu_key',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    /**
     * menu_key yang di-disable eksplisit untuk role+module tersebut. Menu tanpa
     * baris sama sekali dianggap enabled (default terbuka sebelum diatur admin).
     */
    public static function disabledMenuKeys(string $role, string $moduleKey): Collection
    {
        return static::query()
            ->where('role', $role)
            ->where('module_key', $moduleKey)
            ->where('enabled', false)
            ->pluck('menu_key');
    }

    /**
     * Filter tree menu (format config/one_*_menu.php) berdasarkan akses role.
     * Parent tetap tampil kalau punya minimal satu child yang enabled, walau
     * parent itu sendiri di-disable — supaya child-nya tetap terjangkau.
     */
    public static function filterMenuTree(array $menuTree, string $role, string $moduleKey): array
    {
        $disabled = static::disabledMenuKeys($role, $moduleKey);

        return static::filterItems($menuTree, $disabled);
    }

    private static function filterItems(array $items, Collection $disabled): array
    {
        $result = [];

        foreach ($items as $item) {
            if (isset($item['children'])) {
                $children = static::filterItems($item['children'], $disabled);

                if ($children !== [] || ! $disabled->contains($item['menu_key'])) {
                    $result[] = [...$item, 'children' => $children];
                }

                continue;
            }

            if (! $disabled->contains($item['menu_key'])) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
