<?php

namespace App\Services;

use App\Imports\BeritaAcaraImport;
use App\Models\BeritaAcara;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class BeritaAcaraService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return BeritaAcara::query()
            ->with('employee')
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): BeritaAcara
    {
        $data['nomor_surat'] = BeritaAcara::nextNomorSurat($data['tanggal']);
        $data['created_by'] = $createdBy;

        $beritaAcara = BeritaAcara::create($data)->fresh();

        BeritaAcara::resyncNomorSurat();

        return $beritaAcara->fresh();
    }

    public function update(BeritaAcara $beritaAcara, array $data, ?int $updatedBy): BeritaAcara
    {
        $data['updated_by'] = $updatedBy;
        $beritaAcara->update($data);

        BeritaAcara::resyncNomorSurat();

        return $beritaAcara->fresh();
    }

    public function delete(BeritaAcara $beritaAcara): void
    {
        $beritaAcara->delete();

        BeritaAcara::resyncNomorSurat();
    }

    public function importFromExcel(UploadedFile $file, ?int $createdBy): array
    {
        $import = new BeritaAcaraImport($createdBy);

        Excel::import($import, $file);

        return [
            'imported' => $import->importedCount(),
            'invalidDates' => $import->invalidDateCount(),
        ];
    }
}
