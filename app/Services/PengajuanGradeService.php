<?php

namespace App\Services;

use App\Exceptions\PengajuanGradeException;
use App\Models\OpdPengajuanGrade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PengajuanGradeService
{
    /**
     * Pengajuan satu tahun. Untuk tahun berjalan, kolom yang berasal dari Monitoring Grade selalu memakai data terbarunya
     * (bukan salinan saat diajukan); tahun lampau tetap memakai salinan yang tersimpan sebagai arsip.
     */
    public function rows(int $year): Collection
    {
        $rows = OpdPengajuanGrade::where('tahun', $year)->orderBy('nama')->orderBy('nrp')->orderBy('termin')->get();
        if ($year !== (int) date('Y')) {
            return $rows;
        }

        $sources = DB::table('odp_mg_grade_v4')->whereIn('nrp', $rows->pluck('nrp'))->orderBy('id')->get()->keyBy('nrp');

        return $rows->each(function (OpdPengajuanGrade $row) use ($sources) {
            $source = $sources->get($row->nrp);
            if ($source) {
                $row->fill($this->monitoringValues($source));
            }
        });
    }

    /** Daftar tahun (terbaru dulu), selalu memuat tahun berjalan dan tahun yang sedang dilihat. */
    public function years(int $selected): array
    {
        return OpdPengajuanGrade::query()->distinct()->pluck('tahun')
            ->push((int) date('Y'), $selected)
            ->unique()->sortDesc()->values()->all();
    }

    /** @return array<string, int[]> nrp => termin yang sudah terpakai pada tahun tsb */
    public function usedTermins(int $year): array
    {
        return OpdPengajuanGrade::where('tahun', $year)->orderBy('termin')->get(['nrp', 'termin'])
            ->groupBy('nrp')->map(fn ($rows) => $rows->pluck('termin')->all())->all();
    }

    /** Cari karyawan di Monitoring Grade (NRP / nama / posisi), satu baris per NRP. */
    public function searchMonitoring(string $keyword): Collection
    {
        return DB::table('odp_mg_grade_v4')
            ->where(fn ($q) => $q->where('nrp', 'like', "%{$keyword}%")->orWhere('nama', 'like', "%{$keyword}%")->orWhere('posisi', 'like', "%{$keyword}%"))
            ->groupBy('nrp')
            ->orderByRaw('MAX(nama)')
            ->limit(10)
            ->selectRaw('nrp, MAX(nama) as name, MAX(posisi) as jabatan')
            ->get();
    }

    /** Ajukan karyawan dari Monitoring Grade pada termin & tahun tertentu; data diambil dari baris Monitoring Grade. */
    public function ajukanFromMonitoring(string $nrp, int $termin, string $user, ?int $year = null): OpdPengajuanGrade
    {
        $source = DB::table('odp_mg_grade_v4')->where('nrp', $nrp)->orderByDesc('id')->first();
        if (! $source) {
            throw new PengajuanGradeException('Data karyawan tidak ditemukan di Monitoring Grade.');
        }

        $values = $this->monitoringValues($source);

        return $this->create($nrp, (string) $source->nama, $year ?? (int) date('Y'), $termin, $values, $user);
    }

    public function updateField(OpdPengajuanGrade $row, string $field, ?string $value, string $user): void
    {
        $row->update([$field => $this->clean($value), 'updated_by' => $user]);
    }

    /** @return array<string, ?string> kolom pengajuan => nilai dari baris Monitoring Grade */
    private function monitoringValues(object $source): array
    {
        return collect(OpdPengajuanGrade::MONITORING_COLUMNS)->map(fn ($column) => $this->clean($source->{$column}))->all();
    }

    /** Remark + warna dari OneClick HO (lewat API). Mengembalikan false bila pengajuan tidak ada di site ini. */
    public function setRemark(string $nrp, int $year, int $termin, ?string $remark, ?string $color, string $by): bool
    {
        $row = OpdPengajuanGrade::where(['nrp' => $nrp, 'tahun' => $year, 'termin' => $termin])->first();
        if (! $row) {
            return false;
        }

        $row->update(['remark' => $this->clean($remark), 'remark_warna' => $color ?: null, 'updated_by' => mb_substr($by, 0, 100)]);

        return true;
    }

    private function create(string $nrp, string $nama, int $year, int $termin, array $values, string $user): OpdPengajuanGrade
    {
        $exists = OpdPengajuanGrade::where(['nrp' => $nrp, 'tahun' => $year, 'termin' => $termin])->exists();
        if ($exists) {
            throw new PengajuanGradeException("Termin {$termin} tahun {$year} untuk karyawan ini sudah diajukan.");
        }

        return OpdPengajuanGrade::create([
            ...$values,
            'nrp' => $nrp,
            'tahun' => $year,
            'termin' => $termin,
            'nama' => mb_substr($nama, 0, 150),
            'updated_by' => $user,
        ]);
    }

    private function clean(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_substr($value, 0, 255);
    }
}
