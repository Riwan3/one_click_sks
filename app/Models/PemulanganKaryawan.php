<?php

namespace App\Models;

use App\Models\Concerns\HasHashid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PemulanganKaryawan extends Model
{
    use HasHashid;
    protected $fillable = [
        'doc_rank',
        'no_surat',
        'employee_id',
        'nrp',
        'nama',
        'jabatan',
        'departemen',
        'lokasi',
        'tanggal',
        'indikasi_fatigue',
        'jam_tidur_kurang_4jam',
        'fatigue_berat',
        'deskripsi_penyebab',
        'alamat_pengantaran',
        'penanggungjawab_pengantaran',
        'ttd_karyawan',
        'ttd_pengawas',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam_tidur_kurang_4jam' => 'boolean',
            'fatigue_berat' => 'boolean',
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

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
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
                ->orWhere('lokasi', 'like', "%{$keyword}%")
        );
    }

    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('tanggal', '<=', $to));
    }

    public static function nextDocRank(string $tanggal): array
    {
        $year = date('Y', strtotime($tanggal));
        $month = (int) date('n', strtotime($tanggal));

        $docRank = (int) static::query()->whereYear('tanggal', $year)->max('doc_rank') + 1;

        $monthRomans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $siteCode = Setting::getValue('pemulangan_karyawan_site_code', 'ADW') ?: 'ADW';
        $noSurat = sprintf('%03d', $docRank).'/PK/PPA/'.$siteCode.'/PRO/'.$monthRomans[$month - 1].'/'.$year;

        return [$docRank, $noSurat];
    }

    public static function updateSiteCode(string $newSiteCode): void
    {
        $rows = static::query()->whereNotNull('no_surat')->get();
        foreach ($rows as $row) {
            $updated = preg_replace('#(/PPA/)[^/]+(/)#i', '$1'.$newSiteCode.'$2', $row->no_surat);
            if ($updated !== $row->no_surat) {
                $row->no_surat = $updated;
                $row->save();
            }
        }
    }
}
