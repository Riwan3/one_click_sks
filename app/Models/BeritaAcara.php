<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BeritaAcara extends Model
{
    protected $fillable = [
        'nomor_surat',
        'employee_id',
        'tanggal',
        'kronologi',
        'diketahui_oleh_id',
        'ttd_karyawan',
        'ttd_atasan',
        'no_dokumen',
        'revisi',
        'tgl_efektif',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'tgl_efektif' => 'date',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function diketahuiOleh()
    {
        return $this->belongsTo(Employee::class, 'diketahui_oleh_id');
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

        return $query->where(
            fn (Builder $q) => $q->where('nomor_surat', 'like', "%{$keyword}%")
                ->orWhereHas('employee', fn (Builder $eq) => $eq->where('nrp', 'like', "%{$keyword}%")->orWhere('name', 'like', "%{$keyword}%"))
        );
    }

    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('tanggal', '<=', $to));
    }

    public static function nextNomorSurat(string $tanggal): string
    {
        $year = date('Y', strtotime($tanggal));
        $month = (int) date('n', strtotime($tanggal));

        $rank = (int) static::query()->whereYear('tanggal', $year)->count() + 1;

        $monthRomans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        return sprintf('%03d', $rank).'/BA/PPA/ADW/PRO/'.$monthRomans[$month - 1].'/'.$year;
    }

    public static function formatNomorSurat(int $rank, string $tanggal): string
    {
        $year = date('Y', strtotime($tanggal));
        $month = (int) date('n', strtotime($tanggal));
        $monthRomans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        return sprintf('%03d', $rank).'/BA/PPA/ADW/PRO/'.$monthRomans[$month - 1].'/'.$year;
    }

    public static function resyncNomorSurat(): void
    {
        $rows = static::query()->orderBy('tanggal')->orderBy('id')->get(['id', 'tanggal', 'nomor_surat']);

        $ranksByYear = [];

        foreach ($rows as $row) {
            $year = $row->tanggal->format('Y');
            $ranksByYear[$year] = ($ranksByYear[$year] ?? 0) + 1;

            $nomorSurat = static::formatNomorSurat($ranksByYear[$year], $row->tanggal->toDateString());

            if ($nomorSurat !== $row->nomor_surat) {
                static::whereKey($row->id)->update(['nomor_surat' => $nomorSurat]);
            }
        }
    }
}
