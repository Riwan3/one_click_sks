<?php

namespace App\Services;

use App\Models\AttendanceDetail;
use App\Models\CoachingCounseling;
use App\Models\Employee;
use App\Models\HmData;
use App\Models\IzinKaryawan;
use App\Models\OperatorUnfit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    private const MONTH_SHORT_NAMES = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    public function hmStats(int $month, int $year): array
    {
        $row = HmData::query()
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->selectRaw('SUM(hm_diff) as total_hm, COUNT(DISTINCT employee_id) as active_operators, COUNT(*) as total_records, AVG(hm_diff) as avg_hm')
            ->first();

        return [
            'totalHm' => round((float) ($row->total_hm ?? 0), 1),
            'avgHm' => round((float) ($row->avg_hm ?? 0), 1),
            'activeOperators' => (int) ($row->active_operators ?? 0),
            'totalRecords' => (int) ($row->total_records ?? 0),
        ];
    }

    public function hmDailyTrend(int $month, int $year): array
    {
        $rows = HmData::query()
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->selectRaw('tanggal, SUM(hm_diff) as total_hm')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        return $rows->map(fn ($row) => [
            'label' => Carbon::parse($row->tanggal)->format('d'),
            'value' => round((float) $row->total_hm, 1),
        ])->all();
    }

    public function hmYearlyTrend(int $year): array
    {
        $driver = DB::connection()->getDriverName();
        $monthFormat = $driver === 'sqlite' ? "strftime('%m', tanggal)" : "MONTH(tanggal)";

        $rows = HmData::query()
            ->whereYear('tanggal', $year)
            ->selectRaw("{$monthFormat} as bulan, SUM(hm_diff) as total_hm")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy(fn ($item) => (int) $item->bulan);

        return collect(range(1, 12))->map(fn (int $month) => [
            'label' => self::MONTH_SHORT_NAMES[$month],
            'value' => round((float) ($rows->get($month)->total_hm ?? 0), 1),
        ])->all();
    }

    public function hmTopOperators(int $month, int $year, int $limit = 10): array
    {
        $rows = HmData::query()
            ->with('employee:id,name')
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->selectRaw('employee_id, SUM(hm_diff) as total_hm')
            ->groupBy('employee_id')
            ->orderByDesc('total_hm')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($row) => [
            'label' => $row->employee?->name ?? '-',
            'value' => round((float) $row->total_hm, 1),
        ])->all();
    }

    /**
     * Dashboard "Deviasi" — unfit dihitung dari operator_unfits (data yang sama dipakai
     * menu Operator Unfit), bukan flag Employee.status_karyawan yang bisa berbeda dari
     * histori unfit sebenarnya. Jabatan/lokasi tetap di-join dari Employee karena
     * operator_unfits sendiri tidak menyimpan kedua kolom itu.
     */
    public function deviasiStats(): array
    {
        $unfitQuery = OperatorUnfit::query()->activeOnly();

        $unfitByLokasi = (clone $unfitQuery)
            ->leftJoin('employees', 'employees.nrp', '=', 'operator_unfits.nrp')
            ->selectRaw("COALESCE(NULLIF(employees.lokasi_kerja, ''), '-') as lokasi, COUNT(DISTINCT operator_unfits.nrp) as jumlah")
            ->groupBy('lokasi')
            ->orderByDesc('jumlah')
            ->get()
            ->map(fn ($row) => ['label' => $row->lokasi, 'value' => (int) $row->jumlah])
            ->all();

        $unfitList = (clone $unfitQuery)
            ->leftJoin('employees', 'employees.nrp', '=', 'operator_unfits.nrp')
            ->orderBy('operator_unfits.nama')
            ->limit(20)
            ->get(['operator_unfits.nrp', 'operator_unfits.nama', 'employees.jabatan_ss6', 'employees.lokasi_kerja']);

        return [
            'unfitTotal' => (int) (clone $unfitQuery)->distinct('nrp')->count('nrp'),
            'unfitByLokasi' => $unfitByLokasi,
            'unfitList' => $unfitList->map(fn ($row) => [
                'nrp' => $row->nrp,
                'nama' => $row->nama,
                'jabatan' => $row->jabatan_ss6,
                'lokasi' => $row->lokasi_kerja,
            ])->all(),
            'emergencyTotal' => IzinKaryawan::query()->where('jenis_izin', 'emergency')->count(),
            'emergencyBulanIni' => IzinKaryawan::query()
                ->where('jenis_izin', 'emergency')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'emergencyByMonth' => $this->emergencyByMonth(),
            'recentEmergency' => $this->recentEmergency(),
        ];
    }

    /**
     * Label kehadiran per grup kode (padanan klasifikasi SIA di Attendance/Attendance Ratio),
     * dipakai untuk rekap "Rekap Status Kehadiran" di dashboard. Kode di luar daftar ini
     * (mis. hasil impor lama yang belum ter-normalisasi) jatuh ke 'Lainnya'.
     */
    private const ATTENDANCE_STATUS_CASE = "
        CASE LEFT(TRIM(code), 1)
            WHEN 'D' THEN 'Hadir (Siang)'
            WHEN 'N' THEN 'Hadir (Malam)'
            WHEN 'S' THEN 'Sakit'
            WHEN 'I' THEN 'Izin'
            WHEN 'A' THEN 'Alpa'
            WHEN 'T' THEN 'Training'
            WHEN 'C' THEN 'Cuti'
            WHEN 'O' THEN 'Off'
            WHEN 'H' THEN 'Libur'
            ELSE 'Lainnya'
        END
    ";

    /** Dashboard "Kehadiran" — dihitung dari attendance_details, data yang sama dipakai menu Attendance. */
    public function attendanceStats(int $month, int $year): array
    {
        $totals = AttendanceDetail::query()
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->selectRaw("
                COUNT(*) as total_records,
                COUNT(DISTINCT nrp) as total_employees,
                SUM(CASE WHEN LEFT(TRIM(code), 1) IN ('D', 'N') THEN 1 ELSE 0 END) as hadir_count,
                SUM(CASE WHEN LEFT(TRIM(code), 1) IN ('S', 'I', 'A') THEN 1 ELSE 0 END) as non_hadir_count
            ")
            ->first();

        $byStatus = AttendanceDetail::query()
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->selectRaw(self::ATTENDANCE_STATUS_CASE.' as status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->orderByDesc('jumlah')
            ->get()
            ->map(fn ($row) => ['label' => $row->status, 'value' => (int) $row->jumlah])
            ->all();

        return [
            'stats' => [
                'totalRecords' => (int) ($totals->total_records ?? 0),
                'hadirCount' => (int) ($totals->hadir_count ?? 0),
                'nonHadirCount' => (int) ($totals->non_hadir_count ?? 0),
                'totalEmployees' => (int) ($totals->total_employees ?? 0),
            ],
            'byStatus' => $byStatus,
            'dailyTrend' => $this->attendanceDailyTrend($month, $year),
            'topAbsent' => $this->attendanceTopAbsent($month, $year),
        ];
    }

    private function attendanceDailyTrend(int $month, int $year): array
    {
        $rows = AttendanceDetail::query()
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->whereRaw("LEFT(TRIM(code), 1) IN ('S', 'I', 'A')")
            ->selectRaw('tanggal, COUNT(*) as jumlah')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        return $rows->map(fn ($row) => [
            'label' => Carbon::parse($row->tanggal)->format('d'),
            'value' => (int) $row->jumlah,
        ])->all();
    }

    private function attendanceTopAbsent(int $month, int $year, int $limit = 10): array
    {
        $rows = AttendanceDetail::query()
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->selectRaw("
                nrp,
                MAX(name) as name,
                SUM(CASE WHEN LEFT(TRIM(code), 1) IN ('S', 'I', 'A') THEN 1 ELSE 0 END) as total_absent
            ")
            ->groupBy('nrp')
            ->havingRaw('total_absent > 0')
            ->orderByDesc('total_absent')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($row) => [
            'label' => $row->name ?: '-',
            'value' => (int) $row->total_absent,
        ])->all();
    }

    private function emergencyByMonth(): array
    {
        $base = now()->startOfMonth();
        $months = collect(range(11, 0))->mapWithKeys(
            fn (int $i) => [(clone $base)->subMonths($i)->format('Y-m') => 0]
        );

        $driver = DB::connection()->getDriverName();
        $ymFormat = $driver === 'sqlite' ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')";

        $rows = IzinKaryawan::query()
            ->where('jenis_izin', 'emergency')
            ->where('created_at', '>=', (clone $base)->subMonths(11))
            ->selectRaw("{$ymFormat} as ym, COUNT(*) as jumlah")
            ->groupBy('ym')
            ->get();

        foreach ($rows as $row) {
            if ($months->has($row->ym)) {
                $months[$row->ym] = (int) $row->jumlah;
            }
        }

        return $months->map(fn (int $value, string $ym) => [
            'label' => self::MONTH_SHORT_NAMES[(int) substr($ym, 5, 2)],
            'value' => $value,
        ])->values()->all();
    }

    private function recentEmergency(int $limit = 10): array
    {
        return IzinKaryawan::query()
            ->where('jenis_izin', 'emergency')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['no_surat', 'nama', 'nrp', 'jabatan', 'tgl_berangkat', 'tgl_kembali', 'status', 'created_at'])
            ->map(fn (IzinKaryawan $izin) => [
                'noSurat' => $izin->no_surat,
                'nama' => $izin->nama,
                'nrp' => $izin->nrp,
                'jabatan' => $izin->jabatan,
                'tglBerangkat' => $izin->tgl_berangkat?->format('Y-m-d'),
                'tglKembali' => $izin->tgl_kembali?->format('Y-m-d'),
                'status' => $izin->status?->value ?? (string) $izin->status,
                'createdAt' => $izin->created_at?->format('Y-m-d H:i'),
            ])->all();
    }

    /**
     * Dashboard "Coaching Counseling" — pemerataan pembinaan (padanan V1's DashboardController::index()
     * type=coaching branch). Karyawan aktif dibagi jadi "belum pernah di-CC" vs "sudah pernah",
     * dengan detail siapa saja yang paling jarang/lama tidak di-coaching.
     */
    public function coachingStats(int $year, ?string $search = null): array
    {
        $totalKaryawan = Employee::query()->activeOnly()->count();

        $counts = CoachingCounseling::query()
            ->whereHas('employee', fn (Builder $q) => $q->activeOnly())
            ->selectRaw('employee_id, COUNT(*) as total_cc, MAX(tanggal) as last_cc_date')
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $totalSudahPernah = $counts->count();
        $totalBelumPernah = max(0, $totalKaryawan - $totalSudahPernah);

        $idsWithCcTahun = CoachingCounseling::query()
            ->whereHas('employee', fn (Builder $q) => $q->activeOnly())
            ->whereYear('tanggal', $year)
            ->distinct()
            ->pluck('employee_id');

        return [
            'tahun' => $year,
            'search' => $search,
            'totalKaryawan' => $totalKaryawan,
            'totalCcAll' => (int) CoachingCounseling::query()->whereHas('employee', fn (Builder $q) => $q->activeOnly())->count(),
            'totalCcTahun' => (int) CoachingCounseling::query()->whereHas('employee', fn (Builder $q) => $q->activeOnly())->whereYear('tanggal', $year)->count(),
            'totalBelumPernah' => $totalBelumPernah,
            'totalSudahPernah' => $totalSudahPernah,
            'persenBelumPernah' => $totalKaryawan > 0 ? round($totalBelumPernah / $totalKaryawan * 100, 1) : 0.0,
            'persenSudahPernah' => $totalKaryawan > 0 ? round($totalSudahPernah / $totalKaryawan * 100, 1) : 0.0,
            'totalBelumTahun' => max(0, $totalKaryawan - $idsWithCcTahun->count()),
            'distStats' => $this->coachingDistStats($totalKaryawan, $counts),
            'jobNeverStats' => $this->coachingJobNeverStats($counts->keys(), $search),
            'monthlyTrend' => $this->coachingMonthlyTrend($year),
            'neverCoachedList' => $this->coachingNeverList($counts->keys(), $search),
            'leastCoachedList' => $this->coachingLeastList($counts, $search),
        ];
    }

    /** Terapkan filter pencarian V1 (nrp/name/jabatan_ss6/unit) — lebih luas dari Employee::scopeSearch bawaan. */
    private function applyCoachingSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('nrp', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('jabatan_ss6', 'like', "%{$search}%")
                ->orWhere('unit', 'like', "%{$search}%")
        );
    }

    private function coachingDistStats(int $totalKaryawan, Collection $counts): array
    {
        $buckets = ['c0' => max(0, $totalKaryawan - $counts->count()), 'c1' => 0, 'c2' => 0, 'c3' => 0, 'c4' => 0, 'c5_plus' => 0];

        foreach ($counts as $row) {
            $key = match (true) {
                (int) $row->total_cc <= 4 => 'c'.(int) $row->total_cc,
                default => 'c5_plus',
            };
            $buckets[$key]++;
        }

        return [
            ['label' => '0x Sesi', 'value' => $buckets['c0']],
            ['label' => '1x Sesi', 'value' => $buckets['c1']],
            ['label' => '2x Sesi', 'value' => $buckets['c2']],
            ['label' => '3x Sesi', 'value' => $buckets['c3']],
            ['label' => '4x Sesi', 'value' => $buckets['c4']],
            ['label' => '≥5x Sesi', 'value' => $buckets['c5_plus']],
        ];
    }

    /** Top 6 jabatan dengan karyawan belum-pernah-CC terbanyak, sisanya digabung jadi "Lainnya". */
    private function coachingJobNeverStats(Collection $coachedIds, ?string $search): array
    {
        $rows = $this->applyCoachingSearch(
            Employee::query()->activeOnly()->whereNotIn('id', $coachedIds),
            $search
        )
            ->selectRaw("COALESCE(NULLIF(jabatan_ss6, ''), 'Lainnya') as jabatan, COUNT(*) as jumlah")
            ->groupBy('jabatan')
            ->orderByDesc('jumlah')
            ->get();

        $top = $rows->take(6);
        $sisa = (int) $rows->slice(6)->sum('jumlah');

        $result = $top->map(fn ($row) => ['label' => $row->jabatan, 'value' => (int) $row->jumlah])->values();

        if ($sisa > 0) {
            $result->push(['label' => 'Lainnya', 'value' => $sisa]);
        }

        return $result->all();
    }

    private function coachingMonthlyTrend(int $year): array
    {
        $rows = CoachingCounseling::query()
            ->whereHas('employee', fn (Builder $q) => $q->activeOnly())
            ->whereYear('tanggal', $year)
            ->selectRaw('MONTH(tanggal) as bulan, COUNT(*) as jumlah')
            ->groupBy('bulan')
            ->get()
            ->keyBy(fn ($row) => (int) $row->bulan);

        return collect(range(1, 12))->map(fn (int $month) => [
            'label' => self::MONTH_SHORT_NAMES[$month],
            'value' => (int) ($rows->get($month)->jumlah ?? 0),
        ])->all();
    }

    private function coachingNeverList(Collection $coachedIds, ?string $search): array
    {
        return $this->applyCoachingSearch(
            Employee::query()->activeOnly()->whereNotIn('id', $coachedIds),
            $search
        )
            ->with('groupLeader:id,name')
            ->orderBy('name')
            ->get(['id', 'nrp', 'name', 'jabatan_ss6', 'unit', 'foto', 'lokasi_kerja', 'gl_id'])
            ->map(fn (Employee $employee) => [
                'id' => $employee->id,
                'nrp' => $employee->nrp,
                'name' => $employee->name,
                'jabatan' => $employee->jabatan_ss6,
                'unit' => $employee->unit,
                'fotoUrl' => $employee->foto ? asset('storage/'.$employee->foto) : null,
                'lokasi' => $employee->lokasi_kerja,
                'glName' => $employee->groupLeader?->name,
            ])->all();
    }

    private function coachingLeastList(Collection $counts, ?string $search): array
    {
        $employees = $this->applyCoachingSearch(
            Employee::query()->activeOnly()->whereIn('id', $counts->keys()),
            $search
        )
            ->with('groupLeader:id,name')
            ->get(['id', 'nrp', 'name', 'jabatan_ss6', 'unit', 'gl_id'])
            ->keyBy('id');

        return $counts
            ->filter(fn ($row, $employeeId) => $employees->has($employeeId))
            ->map(function ($row, $employeeId) use ($employees) {
                $employee = $employees[$employeeId];
                $lastCcDate = $row->last_cc_date ? Carbon::parse($row->last_cc_date) : null;

                return [
                    'id' => $employee->id,
                    'nrp' => $employee->nrp,
                    'name' => $employee->name,
                    'jabatan' => $employee->jabatan_ss6,
                    'unit' => $employee->unit,
                    'glName' => $employee->groupLeader?->name,
                    'totalCc' => (int) $row->total_cc,
                    'lastCcDate' => $lastCcDate?->format('Y-m-d'),
                    'daysSinceLast' => $lastCcDate ? (int) $lastCcDate->diffInDays(now()) : null,
                ];
            })
            ->sort(fn ($a, $b) => $a['totalCc'] <=> $b['totalCc']
                ?: strcmp((string) $a['lastCcDate'], (string) $b['lastCcDate'])
                ?: strcmp($a['name'], $b['name']))
            ->take(100)
            ->values()
            ->all();
    }
}
