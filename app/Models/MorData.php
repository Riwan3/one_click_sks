<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MorData extends Model
{
    protected $table = 'mor_data';

    protected $fillable = [
        'employee_id',
        'posisi',
        'gl_name',
        'bulan',
        'tahun',
        'atr_persen',
        'atr_point',
        'work_day',
        'sakit',
        'ijin',
        'alpa',
        'timesheet_persen',
        'timesheet_point',
        'hm_ratio',
        'hm_point',
        'hm_jam',
        'hm_target',
        'fuel_persen',
        'fuel_point',
        'fuel_actual',
        'fuel_limit',
        'pdty_persen',
        'pdty_point',
        'act_coal',
        'targt_coal',
        'act_ob',
        'targt_ob',
        'pembinaan',
        'pembinaan_poin',
        'ket_pembinaan',
        'jumlah_point',
        'total_poin',
        'ranking',
        'dari_operator',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->whereHas(
            'employee',
            fn (Builder $q) => $q->where('nrp', 'like', "%{$keyword}%")
                ->orWhere('name', 'like', "%{$keyword}%")
        );
    }

    public function scopePeriod(Builder $query, int $bulan, int $tahun): Builder
    {
        return $query->where('bulan', $bulan)->where('tahun', $tahun);
    }

    /**
     * PDTY hanya berlaku untuk posisi produksi (hauler & alat berat tertentu).
     */
    public function isProduksi(): bool
    {
        $posisi = strtoupper((string) $this->posisi);

        foreach (['HAULER', '395', '1250', '2000', '6015', 'HD', '777'] as $needle) {
            if (str_contains($posisi, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Target pencapaian per kategori, mengikuti aturan V1 (beda untuk produksi vs support).
     */
    public function targets(): array
    {
        $isProduksi = $this->isProduksi();

        return [
            'atr' => $isProduksi ? 15 : 25,
            'timesheet' => 10,
            'hm' => $isProduksi ? 10 : 20,
            'fuel' => $isProduksi ? 5 : 15,
            'pdty' => $isProduksi ? 40 : 0,
            'pembinaan' => $isProduksi ? 20 : 30,
        ];
    }
}
