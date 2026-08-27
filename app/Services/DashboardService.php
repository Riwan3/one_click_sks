<?php

namespace App\Services;

use App\Enums\EmployeeStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\HmData;
use App\Models\IzinKaryawan;
use App\Models\OperatorUnfit;
use Illuminate\Support\Carbon;
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
        $rows = HmData::query()
            ->whereYear('tanggal', $year)
            ->selectRaw('MONTH(tanggal) as bulan, SUM(hm_diff) as total_hm')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

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

    public function deviasiStats(): array
    {
        $unfitQuery = Employee::query()->where(
            fn ($q) => $q->where('status_karyawan', EmployeeStatus::Unfit->value)
                ->orWhereHas('operatorUnfitRecords', fn ($sub) => $sub->activeOnly())
        );

        $unfitByLokasi = (clone $unfitQuery)
            ->selectRaw("COALESCE(NULLIF(lokasi_kerja, ''), '-') as lokasi, COUNT(*) as jumlah")
            ->groupBy('lokasi')
            ->orderByDesc('jumlah')
            ->get()
            ->map(fn ($row) => ['label' => $row->lokasi, 'value' => (int) $row->jumlah])
            ->all();

        $unfitList = (clone $unfitQuery)
            ->orderBy('name')
            ->limit(20)
            ->get(['nrp', 'name', 'jabatan_ss6', 'lokasi_kerja']);

        return [
            'unfitTotal' => (clone $unfitQuery)->count(),
            'unfitByLokasi' => $unfitByLokasi,
            'unfitList' => $unfitList->map(fn ($employee) => [
                'nrp' => $employee->nrp,
                'nama' => $employee->name,
                'jabatan' => $employee->jabatan_ss6,
                'lokasi' => $employee->lokasi_kerja,
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

    public function attendanceStats(int $month, int $year): array
    {
        $rows = Attendance::query()
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->get();

        $byStatus = $rows->map(fn ($row) => ['label' => $row->status, 'value' => (int) $row->jumlah])->all();
        $totalRecords = (int) $rows->sum('jumlah');
        $hadirCount = (int) ($rows->firstWhere('status', 'Hadir')->jumlah ?? 0);

        return [
            'stats' => [
                'totalRecords' => $totalRecords,
                'hadirCount' => $hadirCount,
                'nonHadirCount' => $totalRecords - $hadirCount,
                'totalEmployees' => Attendance::query()
                    ->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year)
                    ->distinct('employee_id')
                    ->count('employee_id'),
            ],
            'byStatus' => $byStatus,
            'dailyTrend' => $this->attendanceDailyTrend($month, $year),
            'topAbsent' => $this->attendanceTopAbsent($month, $year),
        ];
    }

    private function attendanceDailyTrend(int $month, int $year): array
    {
        $rows = Attendance::query()
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->where('status', '!=', 'Hadir')
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
        $rows = Attendance::query()
            ->with('employee:id,name')
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->where('status', '!=', 'Hadir')
            ->selectRaw('employee_id, COUNT(*) as total_absent')
            ->groupBy('employee_id')
            ->orderByDesc('total_absent')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($row) => [
            'label' => $row->employee?->name ?? '-',
            'value' => (int) $row->total_absent,
        ])->all();
    }

    private function emergencyByMonth(): array
    {
        $base = now()->startOfMonth();
        $months = collect(range(11, 0))->mapWithKeys(
            fn (int $i) => [(clone $base)->subMonths($i)->format('Y-m') => 0]
        );

        $rows = IzinKaryawan::query()
            ->where('jenis_izin', 'emergency')
            ->where('created_at', '>=', (clone $base)->subMonths(11))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as jumlah")
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
}
