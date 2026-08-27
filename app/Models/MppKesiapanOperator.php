<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['tanggal', 'csa_code', 'operator_count', 'ready_count', 'percentage', 'status'])]
class MppKesiapanOperator extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'operator_count' => 'integer',
            'ready_count' => 'integer',
            'percentage' => 'decimal:2',
        ];
    }

    public function scopeByDate(Builder $query, $date): Builder
    {
        return $query->whereDate('tanggal', $date);
    }

    public function scopeByCsa(Builder $query, string $csaCode): Builder
    {
        return $query->where('csa_code', $csaCode);
    }

    public function scopeDateBetween(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // Accessor: calculate percentage if needed
    public function getPercentageCalculatedAttribute(): float
    {
        if ($this->operator_count === 0) {
            return 0;
        }

        return ($this->ready_count / $this->operator_count) * 100;
    }

    // Accessor: determine color based on percentage
    public function getColorStatusAttribute(): string
    {
        $pct = (float) $this->percentage;

        if ($pct >= 90) {
            return 'Green';
        } elseif ($pct >= 75) {
            return 'Yellow';
        }

        return 'Red';
    }
}
