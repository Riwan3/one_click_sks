<?php

namespace App\Services;

use App\Models\AttendanceDetail;
use App\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, ?string $category, int $page, int $perPage, ?string $exactNrp = null): LengthAwarePaginator
    {
        $query = AttendanceDetail::query();

        if ($exactNrp !== null && $exactNrp !== '') {
            $query->where('nrp', trim($exactNrp));
        } elseif ($search) {
            $query->search($search);
        }

        return $query
            ->dateBetween($dateFrom, $dateTo)
            ->category($category)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function summary(?string $search, ?string $dateFrom, ?string $dateTo, ?string $exactNrp = null): array
    {
        $baseQuery = AttendanceDetail::query();

        if ($exactNrp !== null && $exactNrp !== '') {
            $baseQuery->where('nrp', trim($exactNrp));
        } elseif ($search) {
            $baseQuery->search($search);
        }

        $baseQuery->dateBetween($dateFrom, $dateTo);

        $totalRecords = (clone $baseQuery)->count();
        $totalSiang = (clone $baseQuery)->where('sia_category', 'D')->count();
        $totalMalam = (clone $baseQuery)->where('sia_category', 'N')->count();
        $totalSakit = (clone $baseQuery)->where('sia_category', 'S')->count();
        $totalIzin = (clone $baseQuery)->where('sia_category', 'I')->count();
        $totalAlpa = (clone $baseQuery)->where('sia_category', 'A')->count();

        $totalAtrRecorded = $totalSiang + $totalMalam + $totalSakit + $totalIzin + $totalAlpa;
        $atrPercentage = $totalAtrRecorded > 0 ? round((($totalSiang + $totalMalam) / $totalAtrRecorded) * 100, 2) : 0;

        return [
            'totalRecords' => $totalRecords,
            'totalSiang' => $totalSiang,
            'totalMalam' => $totalMalam,
            'totalSakit' => $totalSakit,
            'totalIzin' => $totalIzin,
            'totalAlpa' => $totalAlpa,
            'atrPercentage' => $atrPercentage,
        ];
    }

    public function delete(AttendanceDetail $detail): void
    {
        $detail->delete();
    }

    public function importFromApi(int $bulan, int $tahun, string $dept = 'PRO'): array
    {
        if ($bulan < 1 || $bulan > 12 || $tahun < 2000) {
            return ['success' => false, 'message' => 'Parameter bulan/tahun tidak valid.', 'count' => 0];
        }

        $startDate = sprintf('%04d-%02d-01', $tahun, $bulan);
        $endDate = date('Y-m-t', strtotime($startDate));

        $hoApiService = new HoApiService;
        $apiResult = $hoApiService->fetchAttendanceFromApi($startDate, $endDate, $dept);

        if (! $apiResult['success']) {
            return ['success' => false, 'message' => $apiResult['message'], 'count' => 0];
        }

        // Delete existing Attendance details for this period
        AttendanceDetail::whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan)->delete();

        $employeesByNrp = Employee::pluck('id', 'nrp')->all();

        $count = 0;
        $chunk = [];

        foreach ($apiResult['items'] as $item) {
            $rawDate = (string) ($item['date'] ?? $item['tanggal'] ?? '');
            $tanggal = null;

            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $rawDate, $m)) {
                $tanggal = "{$m[3]}-{$m[2]}-{$m[1]}";
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)) {
                $tanggal = $rawDate;
            }

            if (! $tanggal) {
                continue;
            }

            $nrp = trim((string) ($item['nrp'] ?? ''));
            if ($nrp === '') {
                continue;
            }

            $code = trim((string) ($item['code'] ?? ''));
            $siaCat = $hoApiService->classifyAttendanceCode($code);

            $employeeId = $employeesByNrp[$nrp] ?? null;

            $chunk[] = [
                'nrp' => $nrp,
                'name' => $item['name'] ?? null,
                'position' => $item['position'] ?? null,
                'tanggal' => $tanggal,
                'jam_in' => $item['jam_in'] ?? null,
                'jam_out' => $item['jam_out'] ?? null,
                'job' => $item['job'] ?? null,
                'dept' => $item['dept'] ?? null,
                'company' => $item['company'] ?? null,
                'code' => $code,
                'sia_category' => $siaCat,
                'device' => $item['device'] ?? null,
                'owner' => $item['owner'] ?? null,
                'device_count' => (int) ($item['device_count'] ?? 0),
                'device_id' => $item['device_id'] ?? null,
                'employee_id' => $employeeId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $count++;

            if (count($chunk) >= 500) {
                AttendanceDetail::insert($chunk);
                $chunk = [];
            }
        }

        if (! empty($chunk)) {
            AttendanceDetail::insert($chunk);
        }

        return ['success' => true, 'message' => "Bulan {$bulan}/{$tahun}: {$count} data Attendance berhasil diimpor dari API HO.", 'count' => $count];
    }
}
