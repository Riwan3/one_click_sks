<?php

namespace App\Services;

use App\Imports\SkPembinaanImport;
use App\Models\SkPembinaan;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class SkPembinaanService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, ?string $status, int $page, int $perPage): LengthAwarePaginator
    {
        return SkPembinaan::query()
            ->with(['employee', 'pengawas'])
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->statusPembinaan($status)
            ->orderByDesc('dikeluarkan_tanggal')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): SkPembinaan
    {
        $data['nomor_surat'] = SkPembinaan::nextNumber($data['dikeluarkan_tanggal'], $data['perusahaan'] ?? null);
        $data['created_by'] = $createdBy;

        return SkPembinaan::create($data);
    }

    public function update(SkPembinaan $skPembinaan, array $data, ?int $updatedBy): SkPembinaan
    {
        $data['updated_by'] = $updatedBy;
        $skPembinaan->update($data);

        return $skPembinaan;
    }

    public function delete(SkPembinaan $skPembinaan): void
    {
        $skPembinaan->delete();
    }

    public function importFromExcel(UploadedFile $file, ?int $createdBy): int
    {
        $import = new SkPembinaanImport($createdBy);

        Excel::import($import, $file);

        return $import->importedCount();
    }
}
