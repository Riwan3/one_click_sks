<?php

namespace App\Services;

use App\Imports\HmDataImport;
use App\Models\HmData;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class HmDataService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, ?int $employeeId, int $page, int $perPage): LengthAwarePaginator
    {
        return HmData::query()
            ->with('employee')
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->forEmployee($employeeId)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function summary(?string $search, ?string $dateFrom, ?string $dateTo, ?int $employeeId): array
    {
        $query = HmData::query()
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->forEmployee($employeeId);

        return [
            'totalHm' => (float) (clone $query)->sum('hm_diff'),
            'avgHm' => round((float) (clone $query)->avg('hm_diff'), 2),
            'totalRecords' => (clone $query)->count(),
        ];
    }

    public function create(array $data, ?int $createdBy): HmData
    {
        $data['hm_diff'] = max(0, $data['hm_stop'] - $data['hm_start']);
        $data['created_by'] = $createdBy;

        return HmData::create($data);
    }

    public function update(HmData $hmData, array $data): HmData
    {
        $data['hm_diff'] = max(0, $data['hm_stop'] - $data['hm_start']);
        $hmData->update($data);

        return $hmData;
    }

    public function delete(HmData $hmData): void
    {
        $hmData->delete();
    }

    public function importFromExcel(UploadedFile $file, ?int $createdBy): int
    {
        $import = new HmDataImport($createdBy);

        Excel::import($import, $file);

        return $import->importedCount();
    }

    public function importFromApi(int $bulan, int $tahun, ?int $createdBy): array
    {
        if ($bulan < 1 || $bulan > 12 || $tahun < 2000) {
            return ['success' => false, 'message' => 'Parameter bulan/tahun tidak valid.', 'count' => 0];
        }

        $startDate = sprintf('%04d-%02d-01', $tahun, $bulan);
        $endDate = date('Y-m-t', strtotime($startDate));

        $hoApiService = new HoApiService;
        $apiResult = $hoApiService->fetchHmFromApi($startDate, $endDate, 'all');

        if (! $apiResult['success']) {
            return ['success' => false, 'message' => $apiResult['message'], 'count' => 0];
        }

        // Delete existing HM data for this period
        HmData::whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan)->delete();

        $count = 0;
        foreach ($apiResult['items'] as $item) {
            $rawDate = (string) ($item['date'] ?? '');
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

            $optName = trim((string) ($item['opt_name'] ?? 'Karyawan API'));

            // Ensure employee exists
            $employee = \App\Models\Employee::firstOrCreate(
                ['nrp' => $nrp],
                [
                    'name' => $optName,
                    'dept' => $item['dept'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'status_karyawan' => \App\Enums\EmployeeStatus::Fit->value,
                ]
            );

            $hmStart = is_numeric($item['hm_start'] ?? null) ? (float) $item['hm_start'] : 0;
            $hmStop = is_numeric($item['hm_stop'] ?? null) ? (float) $item['hm_stop'] : 0;
            $hmDiff = is_numeric($item['hm_diff'] ?? null) ? (float) $item['hm_diff'] : max(0, $hmStop - $hmStart);

            HmData::updateOrCreate(
                [
                    'tanggal' => $tanggal,
                    'shift' => (string) ($item['shift'] ?? 'all'),
                    'employee_id' => $employee->id,
                    'unit' => (string) ($item['unit'] ?? '-'),
                ],
                [
                    'dept' => $item['dept'] ?? null,
                    'hm_start' => $hmStart,
                    'hm_stop' => $hmStop,
                    'hm_diff' => $hmDiff,
                    'activity' => $item['activity'] ?? null,
                    'parking' => $item['parking'] ?? null,
                    'created_by' => $createdBy,
                ]
            );

            $count++;
        }

        return ['success' => true, 'message' => "Bulan {$bulan}/{$tahun}: {$count} data HM berhasil disimpan dari API HO.", 'count' => $count];
    }
}
