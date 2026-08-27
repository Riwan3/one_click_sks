<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['tanggal', 'nrp', 'nama', 'jabatan', 'departemen', 'unit', 'shift', 'plan_mp'])]
class MppRosterData extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'shift' => 'string',
            'plan_mp' => 'integer',
        ];
    }

    public function scopeDateBetween(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    public function scopeByShift(Builder $query, string $shift): Builder
    {
        return $query->where('shift', $shift);
    }

    public function scopeByDepartmen(Builder $query, string $departmen): Builder
    {
        return $query->where('departemen', $departmen);
    }

    public function scopeByUnit(Builder $query, string $unit): Builder
    {
        return $query->where('unit', $unit);
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('nama', 'like', "%{$keyword}%")
                ->orWhere('nrp', 'like', "%{$keyword}%")
        );
    }
}
