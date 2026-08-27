<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SkPembinaan extends Model
{
    protected $fillable = [
        'nomor_surat',
        'perusahaan',
        'employee_id',
        'tanggal_pelanggaran',
        'jam_pelanggaran',
        'lokasi',
        'berlaku_sampai',
        'spdk_ref',
        'dikeluarkan_di',
        'dikeluarkan_tanggal',
        'ttd_karyawan',
        'ttd_atasan',
        'pengawas_id',
        'jenis_pelanggaran',
        'lubang',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pelanggaran' => 'date',
            'berlaku_sampai' => 'date',
            'dikeluarkan_tanggal' => 'date',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function pengawas()
    {
        return $this->belongsTo(Employee::class, 'pengawas_id');
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('nomor_surat', 'like', "%{$keyword}%")
                ->orWhereHas('employee', fn (Builder $e) => $e->where('nrp', 'like', "%{$keyword}%")->orWhere('name', 'like', "%{$keyword}%"))
        );
    }

    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('dikeluarkan_tanggal', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('dikeluarkan_tanggal', '<=', $to));
    }

    public function scopeStatusPembinaan(Builder $query, ?string $status): Builder
    {
        $today = now()->toDateString();

        return match ($status) {
            'Aktif' => $query->where('dikeluarkan_tanggal', '<=', $today)->where('berlaku_sampai', '>=', $today),
            'Selesai' => $query->where(fn (Builder $q) => $q->where('berlaku_sampai', '<', $today)->orWhere('dikeluarkan_tanggal', '>', $today)),
            default => $query,
        };
    }

    public function statusPembinaan(): string
    {
        $today = now()->toDateString();
        $isActive = $this->dikeluarkan_tanggal?->toDateString() <= $today && $this->berlaku_sampai?->toDateString() >= $today;

        return $isActive ? 'Aktif' : 'Selesai';
    }

    public static function nextNumber(string $tanggal, ?string $perusahaan = null): string
    {
        $year = date('Y', strtotime($tanggal));
        $rank = (int) static::query()->whereYear('dikeluarkan_tanggal', $year)->count() + 1;
        $monthRoman = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][(int) date('n', strtotime($tanggal))];

        $kode = str_contains(strtolower((string) $perusahaan), 'adaro') ? 'AI/PPA/ADW/PRO' : 'PPA/ADW/PRO';

        return sprintf('%03d/%s/%s/%s', $rank, $kode, $monthRoman, $year);
    }
}
