<?php

namespace App\Models;

use App\Models\Concerns\HasHashid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SuratTeguran extends Model
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
        'tanggal_kejadian',
        'tempat_kejadian',
        'jenis_absen',
        'jenis_perilaku',
        'jenis_ceroboh',
        'jenis_hasil_kerja',
        'jenis_menolak_perintah',
        'jenis_pelanggaran_peraturan',
        'penjelasan_singkat',
        'kejadian_tanggal',
        'kejadian_jam',
        'kejadian_lokasi',
        'dikeluarkan_di',
        'dikeluarkan_tanggal',
        'ttd_karyawan',
        'ttd_atasan',
        'ttd_atasan_atasan',
        'catatan',
        'foto',
        'created_by',
        'updated_by',
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
            'tanggal_kejadian' => 'date',
            'kejadian_tanggal' => 'date',
            'dikeluarkan_tanggal' => 'date',
            'jenis_absen' => 'boolean',
            'jenis_perilaku' => 'boolean',
            'jenis_ceroboh' => 'boolean',
            'jenis_hasil_kerja' => 'boolean',
            'jenis_menolak_perintah' => 'boolean',
            'jenis_pelanggaran_peraturan' => 'boolean',
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
        );
    }

    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('tanggal_kejadian', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('tanggal_kejadian', '<=', $to));
    }

    public static function nextDocRank(string $dikeluarkanTanggal): array
    {
        $year = date('Y', strtotime($dikeluarkanTanggal));
        $month = (int) date('n', strtotime($dikeluarkanTanggal));

        $docRank = (int) static::query()->whereYear('dikeluarkan_tanggal', $year)->max('doc_rank') + 1;

        $monthRomans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $siteCode = Setting::getValue('surat_teguran_site_code', 'ADW') ?: 'ADW';
        $noSurat = sprintf('%03d', $docRank).'/ST/PPA/'.$siteCode.'/PRO/'.$monthRomans[$month - 1].'/'.$year;

        return [$docRank, $noSurat];
    }

    public static function updateSiteCode(string $newSiteCode): void
    {
        $rows = static::query()->whereNotNull('no_surat')->get();
        foreach ($rows as $row) {
            $updated = preg_replace('#(/PPA/)[^/]+(/PRO/)#i', '$1'.$newSiteCode.'$2', $row->no_surat);
            if ($updated !== $row->no_surat) {
                $row->no_surat = $updated;
                $row->save();
            }
        }
    }
}
