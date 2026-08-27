<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['jenis', 'unit_code', 'tanggal', 'shift', 'nrp', 'nama', 'url', 'watermark_data', 'created_by'])]
class MppUnitPhoto extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'watermark_data' => 'array',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByJenis(Builder $query, string $jenis): Builder
    {
        return $query->where('jenis', $jenis);
    }

    public function scopeByUnit(Builder $query, string $unitCode): Builder
    {
        return $query->where('unit_code', $unitCode);
    }

    public function scopeDateBetween(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    public function scopeByShift(Builder $query, string $shift): Builder
    {
        return $query->where('shift', $shift);
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('nama', 'like', "%{$keyword}%")
                ->orWhere('nrp', 'like', "%{$keyword}%")
                ->orWhere('unit_code', 'like', "%{$keyword}%")
        );
    }
}
