<?php

namespace App\Services;

use App\Imports\SuratTeguranImport;
use App\Models\SuratTeguran;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class SuratTeguranService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return SuratTeguran::query()
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): SuratTeguran
    {
        [$docRank, $noSurat] = SuratTeguran::nextDocRank($data['dikeluarkan_tanggal']);

        $data['doc_rank'] = $docRank;
        $data['no_surat'] = $noSurat;
        $data['created_by'] = $createdBy;

        return SuratTeguran::create($data);
    }

    public function update(SuratTeguran $suratTeguran, array $data, ?int $updatedBy): SuratTeguran
    {
        $data['updated_by'] = $updatedBy;
        $suratTeguran->update($data);

        return $suratTeguran;
    }

    public function delete(SuratTeguran $suratTeguran): void
    {
        $suratTeguran->delete();
    }

    public function importFromExcel(UploadedFile $file, ?int $createdBy): int
    {
        $import = new SuratTeguranImport($createdBy);

        Excel::import($import, $file);

        return $import->importedCount();
    }
}
