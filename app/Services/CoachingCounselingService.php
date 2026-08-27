<?php

namespace App\Services;

use App\Imports\CoachingCounselingImport;
use App\Models\CoachingCounseling;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class CoachingCounselingService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return CoachingCounseling::query()
            ->with(['employee', 'coach'])
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): CoachingCounseling
    {
        $data['nomor_surat'] = CoachingCounseling::nextNomorSurat($data['tanggal']);
        $data['created_by'] = $createdBy;

        return CoachingCounseling::create($data)->fresh();
    }

    public function update(CoachingCounseling $coachingCounseling, array $data, ?int $updatedBy): CoachingCounseling
    {
        $data['updated_by'] = $updatedBy;
        $coachingCounseling->update($data);

        return $coachingCounseling;
    }

    public function delete(CoachingCounseling $coachingCounseling): void
    {
        $coachingCounseling->delete();
    }

    public function importFromExcel(UploadedFile $file, ?int $createdBy): int
    {
        $import = new CoachingCounselingImport($createdBy);
        Excel::import($import, $file);

        return $import->importedCount();
    }
}
