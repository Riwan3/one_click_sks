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
}
