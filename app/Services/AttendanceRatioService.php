<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

/**
 * Attendance Ratio (ATR) - dihitung dari data attendance_details yang sudah ditarik
 * dari API SS6 HO oleh modul Attendance (padanan database/attendance/v3.php di v1).
 * Formula ATR bulanan: (hadir D+N) / (D+N+S+I+A tercatat) x 100. Cuti/Off/Libur/Training
 * dan kode lain di luar D/N/S/I/A tidak ikut dihitung (dianggap netral, bukan absensi).
 */
class AttendanceRatioService
{
    /** Minimum hari tercatat per bulan supaya satu kuartal dianggap lengkap (skip karyawan baru masuk/resign di tengah kuartal). */
    private const ATR_MIN_HARI_PER_BULAN = 29;

    private const QUARTAL_MONTHS = [1 => [1, 2, 3], 2 => [4, 5, 6], 3 => [7, 8, 9], 4 => [10, 11, 12]];

    public function threshold(): float
    {
        return (float) Setting::getValue('atr_threshold', '90');
    }

    public function saveThreshold(float $value): void
    {
        Setting::setValue('atr_threshold', (string) $value);
    }

    /** Baris tabel utama: ATR per karyawan untuk satu bulan/tahun (padanan getV3Summary). */
    public function monthlySummary(array $filters, int $page, int $perPage): array
    {
        $query = $this->baseQuery($filters)
            ->select([
                'ad.nrp',
                DB::raw('MAX(ad.name) as name'),
                DB::raw('MAX(ad.dept) as dept'),
                DB::raw('MAX(ad.position) as jabatan'),
                DB::raw('MAX(ad.job) as posisi'),
                'e.no_telepon as employee_phone',
                'gl.name as gl_name',
                DB::raw("SUM(CASE WHEN LEFT(TRIM(ad.code), 1) = 'D' THEN 1 ELSE 0 END) as d"),
                DB::raw("SUM(CASE WHEN LEFT(TRIM(ad.code), 1) = 'N' THEN 1 ELSE 0 END) as n"),
                DB::raw("SUM(CASE WHEN LEFT(TRIM(ad.code), 1) = 'S' THEN 1 ELSE 0 END) as s"),
                DB::raw("SUM(CASE WHEN LEFT(TRIM(ad.code), 1) = 'I' THEN 1 ELSE 0 END) as i"),
                DB::raw("SUM(CASE WHEN LEFT(TRIM(ad.code), 1) = 'A' THEN 1 ELSE 0 END) as a"),
                DB::raw("SUM(CASE WHEN LEFT(TRIM(ad.code), 1) IN ('D','N') THEN 1 ELSE 0 END) as hadir"),
                DB::raw("SUM(CASE WHEN LEFT(TRIM(ad.code), 1) IN ('D','N','S','I','A') THEN 1 ELSE 0 END) as total_tercatat"),
            ])
            ->groupBy('ad.nrp', 'e.no_telepon', 'gl.name')
            ->orderBy(DB::raw('MAX(ad.name)'));

        $rows = $query->forPage($page, $perPage)->get()->map(function ($row) {
            $row->atr = $row->total_tercatat > 0 ? round(($row->hadir / $row->total_tercatat) * 100, 1) : 0;

            return $row;
        });

        return $rows->all();
    }

    /** ATR bulanan untuk satu NRP saja (dipakai MPR) — null kalau tidak ada data absensi tercatat bulan itu. */
    public function monthlyRatioForNrp(string $nrp, int $bulan, int $tahun): ?array
    {
        $rows = $this->monthlySummary(['bulan' => $bulan, 'tahun' => $tahun, 'nrp_operator' => $nrp], 1, 1);
        $row = $rows[0] ?? null;

        if (! $row) {
            return null;
        }

        return [
            'atr' => (float) $row->atr,
            'sakit' => (int) $row->s,
            'izin' => (int) $row->i,
            'alfa' => (int) $row->a,
        ];
    }

    public function countMonthlySummary(array $filters): int
    {
        return (clone $this->baseQuery($filters))->distinct()->count('ad.nrp');
    }

    /** Kartu ringkasan (total D/N/S/I/A + rata-rata ATR per karyawan) untuk filter yang sama. */
    public function monthlyDashboardStats(array $filters): array
    {
        $row = $this->baseQuery($filters)
            ->selectRaw("
                SUM(CASE WHEN LEFT(TRIM(ad.code), 1) = 'D' THEN 1 ELSE 0 END) as total_d,
                SUM(CASE WHEN LEFT(TRIM(ad.code), 1) = 'N' THEN 1 ELSE 0 END) as total_n,
                SUM(CASE WHEN LEFT(TRIM(ad.code), 1) = 'S' THEN 1 ELSE 0 END) as total_s,
                SUM(CASE WHEN LEFT(TRIM(ad.code), 1) = 'I' THEN 1 ELSE 0 END) as total_i,
                SUM(CASE WHEN LEFT(TRIM(ad.code), 1) = 'A' THEN 1 ELSE 0 END) as total_a
            ")
            ->reorder()
            ->first();

        $atrPerNrp = DB::query()->fromSub(
            $this->baseQuery($filters)->selectRaw("
                ad.nrp,
                SUM(CASE WHEN LEFT(TRIM(ad.code), 1) IN ('D','N') THEN 1 ELSE 0 END) /
                NULLIF(SUM(CASE WHEN LEFT(TRIM(ad.code), 1) IN ('D','N','S','I','A') THEN 1 ELSE 0 END), 0) * 100 as atr_val
            ")->groupBy('ad.nrp'),
            'per_nrp'
        )->avg('atr_val');

        return [
            'total_d' => (int) ($row->total_d ?? 0),
            'total_n' => (int) ($row->total_n ?? 0),
            'total_s' => (int) ($row->total_s ?? 0),
            'total_i' => (int) ($row->total_i ?? 0),
            'total_a' => (int) ($row->total_a ?? 0),
            'avg_atr' => round((float) ($atrPerNrp ?? 0), 2),
        ];
    }

    /** Karyawan dengan ATR di bawah threshold pada kuartal tertentu (padanan getAllLowAtrByQuarter/getSubordinatesLowAtrByQuarter). */
    public function quarterlyLowAtr(float $threshold, int $tahun, int $quartal, ?int $restrictToGlId = null, ?string $restrictToNrp = null, ?string $search = null): array
    {
        [$start, $end] = $this->quarterRange($tahun, $quartal);
        $months = self::QUARTAL_MONTHS[$quartal] ?? self::QUARTAL_MONTHS[1];
        $minBulanExpr = 'LEAST('.implode(', ', array_map(
            fn ($m) => "SUM(CASE WHEN MONTH(ad.tanggal) = {$m} THEN 1 ELSE 0 END)",
            $months
        )).')';

        $query = DB::table('attendance_details as ad')
            ->leftJoin('employees as e', 'e.nrp', '=', 'ad.nrp')
            ->leftJoin('employees as gl', 'gl.id', '=', 'e.gl_id')
            ->whereBetween('ad.tanggal', [$start, $end])
            ->when($restrictToGlId, fn ($q) => $q->where('e.gl_id', $restrictToGlId))
            ->when($restrictToNrp, fn ($q) => $q->where('ad.nrp', $restrictToNrp))
            ->when($search, fn ($q, $s) => $q->where(
                fn ($w) => $w->where('ad.nrp', 'like', "%{$s}%")->orWhere('ad.name', 'like', "%{$s}%")
            ))
            ->select([
                'ad.nrp',
                DB::raw('MAX(ad.name) as name'),
                'e.no_telepon as employee_phone',
                'e.jabatan_ss6',
                'gl.name as gl_name',
                DB::raw("SUM(CASE WHEN LEFT(TRIM(ad.code), 1) IN ('D','N','T') THEN 1 ELSE 0 END) as hadir"),
                DB::raw("SUM(CASE WHEN LEFT(TRIM(ad.code), 1) = 'S' THEN 1 ELSE 0 END) as sakit"),
                DB::raw("SUM(CASE WHEN LEFT(TRIM(ad.code), 1) = 'I' THEN 1 ELSE 0 END) as izin"),
                DB::raw("SUM(CASE WHEN LEFT(TRIM(ad.code), 1) = 'A' THEN 1 ELSE 0 END) as alpa"),
                DB::raw("SUM(CASE WHEN LEFT(TRIM(ad.code), 1) = 'T' THEN 1 ELSE 0 END) as training"),
                DB::raw("SUM(CASE WHEN LEFT(TRIM(ad.code), 1) IN ('D','N','S','I','A','T') THEN 1 ELSE 0 END) as total_tercatat"),
                DB::raw("{$minBulanExpr} as min_bulan_tercatat"),
            ])
            ->groupBy('ad.nrp', 'e.no_telepon', 'e.jabatan_ss6', 'gl.name')
            ->havingRaw('SUM(CASE WHEN LEFT(TRIM(ad.code), 1) IN (\'D\',\'N\',\'T\') THEN 1 ELSE 0 END) > 0')
            ->having('min_bulan_tercatat', '>=', self::ATR_MIN_HARI_PER_BULAN)
            ->havingRaw("ROUND((SUM(CASE WHEN LEFT(TRIM(ad.code), 1) IN ('D','N','T') THEN 1 ELSE 0 END) / SUM(CASE WHEN LEFT(TRIM(ad.code), 1) IN ('D','N','S','I','A','T') THEN 1 ELSE 0 END)) * 100, 1) < ?", [$threshold])
            ->orderBy('gl_name')
            ->orderByRaw("(SUM(CASE WHEN LEFT(TRIM(ad.code), 1) IN ('D','N','T') THEN 1 ELSE 0 END) / SUM(CASE WHEN LEFT(TRIM(ad.code), 1) IN ('D','N','S','I','A','T') THEN 1 ELSE 0 END)) ASC");

        return $query->get()->map(function ($row) {
            $row->atr = $row->total_tercatat > 0 ? round(($row->hadir / $row->total_tercatat) * 100, 1) : 0;

            return $row;
        })->all();
    }

    public function quarterRange(int $tahun, int $quartal): array
    {
        $months = self::QUARTAL_MONTHS[$quartal] ?? self::QUARTAL_MONTHS[1];
        $start = sprintf('%04d-%02d-01', $tahun, $months[0]);
        $end = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $tahun, $months[2])));

        return [$start, $end];
    }

    public function currentQuartal(): int
    {
        return (int) ceil((int) now()->format('n') / 3);
    }

    /** Kuartal terakhir yang sudah selesai penuh (bukan kuartal berjalan). */
    public function lastCompleteQuartal(): array
    {
        $quartal = $this->currentQuartal() - 1;
        $tahun = (int) now()->format('Y');
        if ($quartal < 1) {
            $quartal = 4;
            $tahun--;
        }

        return ['quartal' => $quartal, 'tahun' => $tahun];
    }

    public function quartalLabel(int $quartal): string
    {
        return [
            1 => 'Kuartal 1 (Jan-Mar)', 2 => 'Kuartal 2 (Apr-Jun)',
            3 => 'Kuartal 3 (Jul-Sep)', 4 => 'Kuartal 4 (Okt-Des)',
        ][$quartal] ?? '';
    }

    private function baseQuery(array $filters)
    {
        $bulan = (int) ($filters['bulan'] ?? now()->month);
        $tahun = (int) ($filters['tahun'] ?? now()->year);

        return DB::table('attendance_details as ad')
            ->leftJoin('employees as e', 'e.nrp', '=', 'ad.nrp')
            ->leftJoin('employees as gl', 'gl.id', '=', 'e.gl_id')
            ->whereMonth('ad.tanggal', $bulan)
            ->whereYear('ad.tanggal', $tahun)
            ->when($filters['nrp_operator'] ?? null, fn ($q, $nrp) => $q->where('ad.nrp', $nrp))
            ->when(! ($filters['nrp_operator'] ?? null) ? ($filters['search'] ?? null) : null, fn ($q, $s) => $q->where(
                fn ($w) => $w->where('ad.nrp', 'like', "%{$s}%")->orWhere('ad.name', 'like', "%{$s}%")
            ))
            ->when($filters['kategori'] ?? null, fn ($q, $k) => $q->whereRaw('LEFT(TRIM(ad.code), 1) = ?', [strtoupper($k)]));
    }
}
