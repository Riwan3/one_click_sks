<?php

namespace App\Services;

use App\Imports\AttendanceMonthlyRosterImport;
use App\Models\AttendanceMonthlyRoster;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceMonthlyRosterService
{
    public function paginate(
        ?string $search,
        ?int $bulan,
        ?int $tahun,
        int $page,
        int $perPage,
        ?string $kategori = null,
        bool $filterLowAtr = false,
        float $atrThreshold = 95.0,
        ?int $employeeId = null
    ): LengthAwarePaginator {
        return AttendanceMonthlyRoster::query()
            ->with('employee')
            ->search($search)
            ->period($bulan, $tahun)
            ->kategori($kategori)
            ->lowAtr($filterLowAtr, $atrThreshold)
            ->forEmployee($employeeId)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderBy('employee_id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * 6 summary stat cards from V1 (views/database/attendance/index.php):
     * Shift D count, Shift N count, Sakit count, Izin count, Alfa count, and
     * average ATR% — all computed over the same filtered (search/period/
     * kategori) period, mirroring V1's AttendanceRepository::getDashboardStats().
     */
    public function summaryStats(?string $search, ?int $bulan, ?int $tahun, ?string $kategori = null, ?int $employeeId = null): array
    {
        $query = AttendanceMonthlyRoster::query()
            ->search($search)
            ->period($bulan, $tahun)
            ->kategori($kategori)
            ->forEmployee($employeeId);

        $totals = $query->selectRaw('SUM(d) as total_d, SUM(n) as total_n, SUM(s) as total_s, SUM(i) as total_i, SUM(a) as total_a, SUM(atr_percentage) as total_atr, COUNT(*) as row_count')
            ->first();

        $rowCount = (int) ($totals->row_count ?? 0);
        $avgAtr = $rowCount > 0 ? round(((float) $totals->total_atr) / $rowCount, 2) : 0.0;

        return [
            'total_d' => (int) ($totals->total_d ?? 0),
            'total_n' => (int) ($totals->total_n ?? 0),
            'total_s' => (int) ($totals->total_s ?? 0),
            'total_i' => (int) ($totals->total_i ?? 0),
            'total_a' => (int) ($totals->total_a ?? 0),
            'avg_atr' => $avgAtr,
        ];
    }

    public function create(array $data, ?int $createdBy): AttendanceMonthlyRoster
    {
        $data['created_by'] = $createdBy;

        return AttendanceMonthlyRoster::create($data);
    }

    public function update(AttendanceMonthlyRoster $roster, array $data): AttendanceMonthlyRoster
    {
        $roster->update($data);

        return $roster;
    }

    public function delete(AttendanceMonthlyRoster $roster): void
    {
        $roster->delete();
    }

    public function importFromExcel(UploadedFile $file, int $bulan, int $tahun, ?int $createdBy): array
    {
        $import = new AttendanceMonthlyRosterImport($bulan, $tahun, $createdBy);

        Excel::import($import, $file);

        return [
            'imported' => $import->importedCount(),
            'skipped' => $import->skippedCount(),
        ];
    }
}
