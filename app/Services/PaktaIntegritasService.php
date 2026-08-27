<?php

namespace App\Services;

use App\Imports\PaktaIntegritasImport;
use App\Models\PaktaIntegritas;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PaktaIntegritasService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return PaktaIntegritas::query()
            ->with('employee')
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): PaktaIntegritas
    {
        $data['nomor_surat'] = PaktaIntegritas::nextNomorSurat($data['tanggal']);
        $data['created_by'] = $createdBy;

        return PaktaIntegritas::create($data)->fresh();
    }

    public function update(PaktaIntegritas $paktaIntegritas, array $data): PaktaIntegritas
    {
        $paktaIntegritas->update($data);

        return $paktaIntegritas;
    }

    public function delete(PaktaIntegritas $paktaIntegritas): void
    {
        $paktaIntegritas->delete();
    }

    public function importFromExcel(UploadedFile $file, ?int $createdBy): int
    {
        $import = new PaktaIntegritasImport($createdBy);
        Excel::import($import, $file);

        return $import->importedCount();
    }
}
