<?php

namespace App\Models;

use App\Enums\IzinJenis;
use App\Enums\IzinStatus;
use App\Models\Concerns\HasHashid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class IzinKaryawan extends Model
{
    use HasHashid;
    protected $fillable = [
        'jenis_izin',
        'no_surat',
        'nama',
        'nrp',
        'jabatan',
        'departemen',
        'alasan',
        'tujuan',
        'alamat',
        'tgl_berangkat',
        'tgl_kembali',
        'no_telepon',
        'keluhan',
        'hasil_td',
        'hasil_rr',
        'hasil_t',
        'hasil_p',
        'hasil_spo',
        'diagnosa',
        'terapi',
        'keterangan_status',
        'rujuk_ke',
        'rumah_sakit_rujukan',
        'dokter_rujukan',
        'dipulangkan_tidur',
        'file_bukti',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'jenis_izin' => IzinJenis::class,
            'status' => IzinStatus::class,
            'tgl_berangkat' => 'date',
            'tgl_kembali' => 'date',
            'approved_at' => 'datetime',
            'dipulangkan_tidur' => 'boolean',
        ];
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeJenis(Builder $query, ?string $jenis): Builder
    {
        return $jenis ? $query->where('jenis_izin', $jenis) : $query;
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('nrp', 'like', "%{$keyword}%")
                ->orWhere('nama', 'like', "%{$keyword}%")
                ->orWhere('no_surat', 'like', "%{$keyword}%")
        );
    }

    public function scopeCreatedByUser(Builder $query, ?int $userId): Builder
    {
        return $userId ? $query->where('created_by', $userId) : $query;
    }

    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('created_at', '<=', $to));
    }

    public static function nextSuratNumber(IzinJenis $jenis): string
    {
        $bulan = now()->month;
        $tahun = now()->year;

        $count = static::query()
            ->where('jenis_izin', $jenis->value)
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->count();

        $romanMonths = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];

        return sprintf('%03d/%s/PPA-ADRO/%s/%d', $count + 1, $jenis->suratKode(), $romanMonths[$bulan], $tahun);
    }
}
