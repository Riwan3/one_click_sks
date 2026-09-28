<?php

namespace App\Models;

use App\Models\Concerns\HasHashid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PemanggilanKaryawan extends Model
{
    use HasHashid;
    protected $fillable = [
        'no_surat',
        'employee_id',
        'nrp',
        'nama',
        'jabatan',
        'alasan_pemanggilan',
        'tempat',
        'jam_pemanggilan',
        'tanggal_pemanggilan',
        'ttd_pemanggilan',
        'nama_manpower',
        'fatigue_periode',
        'ttd_pemanggil',
        'ttd_karyawan',
        'eviden_1',
        'eviden_2',
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
            'tanggal_pemanggilan' => 'date',
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
            ->when($from, fn (Builder $q) => $q->whereDate('tanggal_pemanggilan', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('tanggal_pemanggilan', '<=', $to));
    }

    public static function nextNomorSurat(?string $tanggal = null): string
    {
        $tanggal = $tanggal ?: date('Y-m-d');
        $year = date('Y', strtotime($tanggal));
        $month = (int) date('n', strtotime($tanggal));

        $count = (int) static::query()->whereYear('tanggal_pemanggilan', $year)->count() + 1;

        $monthRomans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $siteCode = Setting::getValue('pemanggilan_karyawan_site_code', 'ADW') ?: 'ADW';

        return sprintf('%03d', $count).'/PK/PPA/'.$siteCode.'/PRO/'.$monthRomans[$month - 1].'/'.$year;
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
