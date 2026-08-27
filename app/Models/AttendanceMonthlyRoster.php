<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Monthly aggregate attendance roster — one row per employee per month,
 * rebuilt to match ONECLICK V1's `attendance_data` table (see the migration
 * for the full V1-parity notes and the code-meaning legend).
 */
class AttendanceMonthlyRoster extends Model
{
    /** Status-code columns imported straight from V1's Excel template, in order. */
    public const CODE_COLUMNS = ['d', 'n', 'io', 'nc', 'l', 't', 'o', 's', 'i', 'a', 'k', 'p', 'r', 'h', 'nr'];

    protected $fillable = [
        'employee_id',
        'bulan',
        'tahun',
        'dept',
        'jabatan',
        'posisi',
        'comp',
        'd', 'n', 'io', 'nc', 'l', 't', 'o', 's', 'i', 'a', 'k', 'p', 'r', 'h', 'nr',
        'atr_percentage',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'bulan' => 'integer',
            'tahun' => 'integer',
            'atr_percentage' => 'decimal:2',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->whereHas(
            'employee',
            fn (Builder $q) => $q->where('nrp', 'like', "%{$keyword}%")->orWhere('name', 'like', "%{$keyword}%")
        );
    }

    public function scopePeriod(Builder $query, ?int $bulan, ?int $tahun): Builder
    {
        return $query
            ->when($bulan, fn (Builder $q) => $q->where('bulan', $bulan))
            ->when($tahun, fn (Builder $q) => $q->where('tahun', $tahun));
    }

    /**
     * V1's `atr` column is a free-text value populated by the Excel import,
     * not derived from a formula in AttendanceRepository.php. We honor a
     * stored value first; if it is missing, fall back to a best-effort
     * ratio of "hadir" days (d + n + t, mirroring the codes V1's separate
     * ATR-v2 feature treats as present) over total recorded days across all
     * 15 codes. This fallback is a reasonable approximation only — confirm
     * against V1's original ATR formula if the source spreadsheet is found.
     */
    public function getAtrPercentAttribute(): float
    {
        if ($this->atr_percentage !== null) {
            return (float) $this->atr_percentage;
        }

        $totalRecorded = collect(self::CODE_COLUMNS)->sum(fn (string $code) => (int) $this->{$code});

        if ($totalRecorded <= 0) {
            return 0.0;
        }

        $hadir = (int) $this->d + (int) $this->n + (int) $this->t;

        return round(($hadir / $totalRecorded) * 100, 2);
    }
}
