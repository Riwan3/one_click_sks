<?php

namespace App\Services;

use App\Imports\AttendanceMonthlyRosterImport;
use App\Models\AttendanceMonthlyRoster;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceMonthlyRosterService
{
    public function paginate(?string $search, ?int $bulan, ?int $tahun, int $page, int $perPage): LengthAwarePaginator
    {
        return AttendanceMonthlyRoster::query()
            ->with('employee')
            ->search($search)
            ->period($bulan, $tahun)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderBy('employee_id')
            ->paginate($perPage, ['*'], 'page', $page);
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
