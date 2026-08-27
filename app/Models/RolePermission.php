<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = [
        'role',
        'is_mobile',
    ];

    protected function casts(): array
    {
        return [
            'is_mobile' => 'boolean',
        ];
    }

    public static function isMobile(string $role): bool
    {
        return (bool) static::query()->where('role', $role)->value('is_mobile');
    }
}
