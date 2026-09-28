<?php

namespace App\Models;

use App\Models\Concerns\HasHashid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CoachingCounseling extends Model
{
    use HasHashid;
    protected $fillable = [
        'nomor_surat',
        'employee_id',
        'sp_id',
        'sk_id',
        'tanggal',
        'lokasi',
        'waktu_pertemuan',
        'materi_pribadi',
        'materi_keluarga',
        'materi_lingkungan',
        'materi_pekerjaan',
        'fatigue_level',
        'deskripsi',
        'rekomendasi',
        'file_bukti',
        'file_slip',
        'coach_employee_id',
        'ttd_anak_asuh',
        'ttd_bapa_asuh',
        'created_by',
        'updated_by',
    ];

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

    public function coach()
    {
        return $this->belongsTo(Employee::class, 'coach_employee_id');
    }

    public function suratPeringatan()
    {
        return $this->belongsTo(SuratPeringatan::class, 'sp_id');
    }

    public function skPembinaan()
    {
        return $this->belongsTo(SkPembinaan::class, 'sk_id');
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

    public static function nextNomorSurat(string $tanggal, ?string $site = null): string
    {
        $year = date('Y', strtotime($tanggal));
        $month = (int) date('n', strtotime($tanggal));

        $rank = (int) static::query()->whereYear('tanggal', $year)->count() + 1;

        $monthRomans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $siteCode = $site ?: (Setting::getValue('coaching_counseling_site_code', 'ADW') ?: 'ADW');

        return sprintf('%04d', $rank).'/CC/PPA/'.$siteCode.'/PRO/'.$monthRomans[$month - 1].'/'.$year;
    }

    public static function updateSiteCode(string $newSiteCode): void
    {
        $rows = static::query()->whereNotNull('nomor_surat')->get();
        foreach ($rows as $row) {
            $updated = preg_replace('#(/CC/PPA/)[^/]+(/PRO/)#i', '$1'.$newSiteCode.'$2', $row->nomor_surat);
            if ($updated !== $row->nomor_surat) {
                $row->nomor_surat = $updated;
                $row->save();
            }
        }
    }

    public static function resyncNomorSurat(): void
    {
        $year = date('Y');
        $items = static::query()->whereYear('tanggal', $year)->orderBy('tanggal')->get();
        $siteCode = Setting::getValue('coaching_counseling_site_code', 'ADW') ?: 'ADW';
        $monthRomans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        foreach ($items as $index => $item) {
            $tanggal = $item->tanggal->format('Y-m-d');
            $month = (int) date('n', strtotime($tanggal));
            $rank = $index + 1;

            $item->update([
                'nomor_surat' => sprintf('%04d', $rank).'/CC/PPA/'.$siteCode.'/PRO/'.$monthRomans[$month - 1].'/'.$year,
            ]);
        }
    }
}
