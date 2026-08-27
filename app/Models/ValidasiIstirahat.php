<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ValidasiIstirahat extends Model
{
    protected $fillable = [
        'doc_rank',
        'no_surat',
        'employee_id',
        'nrp',
        'nama',
        'jabatan',
        'departemen',
        'alasan_kurang_tidur',
        'alasan_kurang_tidur_berulang',
        'kendala_tidur',
        'tinggal_di',
        'nama_mess',
        'alamat_non_mess',
        'status_perkawinan',
        'jumlah_anak_serumah',
        'anak_usia_kurang_2th',
        'pekerjaan_sampingan',
        'keterangan',
        'tanggal_validasi',
        'ttd_karyawan',
        'ttd_pengawas',
        'nama_pengawas',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_validasi' => 'date',
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
            fn (Builder $q) => $q->where('nrp', 'like', "%{$keyword}%")
                ->orWhere('nama', 'like', "%{$keyword}%")
                ->orWhere('no_surat', 'like', "%{$keyword}%")
        );
    }

    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('tanggal_validasi', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('tanggal_validasi', '<=', $to));
    }

    public static function nextDocRank(string $tanggal): array
    {
        $year = date('Y', strtotime($tanggal));
        $month = (int) date('n', strtotime($tanggal));

        $docRank = (int) static::query()->whereYear('tanggal_validasi', $year)->max('doc_rank') + 1;

        $monthRomans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $noSurat = sprintf('%03d', $docRank).'/VI/PPA/ADW/PRO/'.$monthRomans[$month - 1].'/'.$year;

        return [$docRank, $noSurat];
    }
}
