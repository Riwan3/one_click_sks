<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['tanggal', 'shift', 'kategori', 'jumlah', 'sakit', 'izin', 'alfa', 'training', 'induksi', 'tidak_checkin', 'created_by', 'updated_by'])]
class MppDailyAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'shift' => 'string',
            'kategori' => 'string',
            'jumlah' => 'integer',
            'sakit' => 'integer',
            'izin' => 'integer',
            'alfa' => 'integer',
            'training' => 'integer',
            'induksi' => 'integer',
            'tidak_checkin' => 'integer',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeDateBetween(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    public function scopeByShift(Builder $query, string $shift): Builder
    {
        return $query->where('shift', $shift);
    }

    public function scopeByKategori(Builder $query, string $kategori): Builder
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where('kategori', 'like', "%{$keyword}%");
    }

    // Calculated field: actual_mp = jumlah - (sakit + izin + alfa + training + induksi + tidak_checkin)
    public function getActualMpAttribute(): int
    {
        $deductions = $this->sakit + $this->izin + $this->alfa + $this->training + $this->induksi + $this->tidak_checkin;

        return max(0, $this->jumlah - $deductions);
    }

    // Calculate total deductions
    public function getTotalDeductionsAttribute(): int
    {
        return $this->sakit + $this->izin + $this->alfa + $this->training + $this->induksi + $this->tidak_checkin;
    }
}
