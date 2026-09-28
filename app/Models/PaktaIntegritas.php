<?php

namespace App\Models;

use App\Models\Concerns\HasHashid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaktaIntegritas extends Model
{
    use HasHashid;
    protected $table = 'pakta_integritas';

    protected $fillable = [
        'employee_id',
        'nomor_surat',
        'tanggal',
        'tempat',
        'pernyataan',
        'atasan_nama',
        'mengetahui_nama',
        'cc',
        'arsip',
        'ttd_karyawan',
        'ttd_atasan',
        'ttd_mengetahui',
        'foto',
        'created_by',
    ];

    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto) {
            return null;
        }

        if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://') || str_starts_with($this->foto, '/storage/')) {
            return $this->foto;
        }

        return '/storage/' . ltrim($this->foto, '/');
    }

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
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

        $records = static::query()
            ->whereYear('tanggal', $year)
            ->get(['nomor_surat']);

        $maxNumber = 0;
        foreach ($records as $record) {
            preg_match('/^(\d+)\//', $record->nomor_surat, $matches);
            if (isset($matches[1])) {
                $num = (int) $matches[1];
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        $rank = $maxNumber + 1;

        $monthRomans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $siteCode = Setting::getValue('pakta_integritas_site_code', 'ADW') ?: 'ADW';

        return sprintf('%03d', $rank).'/PI/PPA/'.$siteCode.'/PRO/'.$monthRomans[$month - 1].'/'.$year;
    }

    public static function updateSiteCode(string $newSiteCode): void
    {
        $rows = static::query()->whereNotNull('nomor_surat')->get();
        foreach ($rows as $row) {
            $updated = preg_replace('#(/PI/PPA/)[^/]+(/PRO/)#i', '$1'.$newSiteCode.'$2', $row->nomor_surat);
            if ($updated !== $row->nomor_surat) {
                $row->nomor_surat = $updated;
                $row->save();
            }
        }
    }

    public static function resyncNomorSurat(): void
    {
        $records = static::query()->orderBy('tanggal')->get();

        foreach ($records as $record) {
            $expectedNumber = static::nextNomorSurat($record->tanggal->toDateString());
            if ($record->nomor_surat !== $expectedNumber) {
                $record->update(['nomor_surat' => $expectedNumber]);
            }
        }
    }
}
