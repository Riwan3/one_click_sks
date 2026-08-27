<?php

namespace App\Models;

use App\Enums\RoleScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'code',
        'label',
        'scope',
        'landing_path',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'scope' => RoleScope::class,
            'is_system' => 'boolean',
        ];
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('code', 'like', "%{$keyword}%")
                ->orWhere('label', 'like', "%{$keyword}%")
        );
    }
}
