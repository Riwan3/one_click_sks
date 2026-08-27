<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RoleModuleAccess extends Model
{
    protected $fillable = [
        'role',
        'module_key',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    /**
     * Set module_key yang diizinkan untuk role tersebut. Role tanpa baris sama
     * sekali dianggap punya akses penuh (default terbuka sebelum diatur eksplisit).
     */
    public static function allowedModuleKeys(string $role): Collection
    {
        $rows = static::query()->where('role', $role)->get();

        if ($rows->isEmpty()) {
            return collect(config('modules.list'))->pluck('key');
        }

        return $rows->where('enabled', true)->pluck('module_key')->values();
    }
}
