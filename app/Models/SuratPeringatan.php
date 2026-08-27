<?php

namespace App\Models;

use App\Enums\JenisPembinaan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SuratPeringatan extends Model
{
    protected $fillable = [
        'employee_id',
        'nomor_sp',
        'kode_pelanggaran',
        'jenis_pembinaan',
        'detail_pelanggaran',
        'tanggal_kejadian',
        'jam_kejadian',
        'tempat_kejadian',
        'pelanggaran_absen_terlambat',
        'pelanggaran_perilaku',
        'pelanggaran_ceroboh_lalai',
        'pelanggaran_hasil_kerja',
        'pelanggaran_menolak_perintah',
        'pelanggaran_peraturan',
        'peringatan_sebelumnya_status',
        'peringatan_sebelumnya_jenis',
        'peringatan_sebelumnya_mengenai',
        'sanksi_masa_berlaku',
        'dikeluarkan_di',
        'dikeluarkan_tanggal',
        'catatan_karyawan',
        'ttd_karyawan',
        'ttd_atasan',
        'admin_id',
        'pengawas_id',
        'koreksi_alpa',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'jenis_pembinaan' => JenisPembinaan::class,
            'tanggal_kejadian' => 'date',
            'dikeluarkan_tanggal' => 'date',
            'pelanggaran_absen_terlambat' => 'boolean',
            'pelanggaran_perilaku' => 'boolean',
            'pelanggaran_ceroboh_lalai' => 'boolean',
            'pelanggaran_hasil_kerja' => 'boolean',
            'pelanggaran_menolak_perintah' => 'boolean',
            'pelanggaran_peraturan' => 'boolean',
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

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('nomor_sp', 'like', "%{$keyword}%")
                ->orWhereHas('employee', fn (Builder $e) => $e->where('nrp', 'like', "%{$keyword}%")->orWhere('name', 'like', "%{$keyword}%"))
        );
    }

    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('dikeluarkan_tanggal', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('dikeluarkan_tanggal', '<=', $to));
    }

    public static function activeFor(int $employeeId, ?string $tanggalRef = null): ?self
    {
        $tanggalRef = Carbon::parse($tanggalRef ?? now());

        return static::query()
            ->where('employee_id', $employeeId)
            ->where('dikeluarkan_tanggal', '<=', $tanggalRef->toDateString())
            ->orderByDesc('dikeluarkan_tanggal')
            ->orderByDesc('id')
            ->get()
            ->first(fn (self $sp) => $sp->dikeluarkan_tanggal->copy()->addMonths($sp->sanksi_masa_berlaku)->greaterThan($tanggalRef));
    }

    public static function nextNumber(string $jenisPembinaan, string $dikeluarkanTanggal): string
    {
        $rank = static::nextRank($dikeluarkanTanggal);
        $jenis = JenisPembinaan::from($jenisPembinaan);
        $year = date('Y', strtotime($dikeluarkanTanggal));
        $monthRoman = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][(int) date('n', strtotime($dikeluarkanTanggal))];

        return sprintf('%03d', $rank).'/SP '.$jenis->spCode().'/PPA/ADW/PRO/'.$monthRoman.'/'.$year;
    }

    public static function nextRank(string $tanggalRef): int
    {
        $year = date('Y', strtotime($tanggalRef));

        return (int) static::query()->whereYear('dikeluarkan_tanggal', $year)->count() + 1;
    }
}
