<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Master Unit Setting Fleet: loader & hauler yang boleh dipakai di setting harian. */
class FleetMasterUnit extends Model
{
    public const LOADER = 'loader';

    public const HAULER = 'hauler';

    protected $fillable = [
        'code', 'unit_type', 'category', 'source_unit_id',
        'default_group', 'default_area', 'default_front', 'default_refueling_time', 'default_distance',
        'is_active', 'sort_order', 'created_by',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'default_distance' => 'integer', 'sort_order' => 'integer'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('unit_type', $type);
    }
}
