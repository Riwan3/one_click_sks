<?php

namespace App\Services;

use App\Imports\SkPembinaanImport;
use App\Models\SkPembinaan;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
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
        $data['nomor_surat'] = ! empty($data['nomor_surat'])
            ? $data['nomor_surat']
            : SkPembinaan::nextNumber($data['dikeluarkan_tanggal'], $data['perusahaan'] ?? null);
        $data['created_by'] = $createdBy;

        if (isset($data['foto']) && $data['foto'] instanceof UploadedFile) {
            $data['foto'] = $data['foto']->store('uploads/sk_pembinaan', 'public');
        } else {
            unset($data['foto']);
        }

        return SkPembinaan::create($data);
    }

    public function update(SkPembinaan $skPembinaan, array $data, ?int $updatedBy): SkPembinaan
    {
        $data['updated_by'] = $updatedBy;

        if (! empty($data['hapus_foto'])) {
            if ($skPembinaan->foto) {
                Storage::disk('public')->delete($skPembinaan->foto);
            }
            $data['foto'] = null;
        } elseif (isset($data['foto']) && $data['foto'] instanceof UploadedFile) {
            if ($skPembinaan->foto) {
                Storage::disk('public')->delete($skPembinaan->foto);
            }
            $data['foto'] = $data['foto']->store('uploads/sk_pembinaan', 'public');
        } else {
            unset($data['foto']);
        }
        unset($data['hapus_foto']);

        $skPembinaan->update($data);

        return $skPembinaan;
    }

    public function delete(SkPembinaan $skPembinaan): void
    {
        if ($skPembinaan->foto) {
            Storage::disk('public')->delete($skPembinaan->foto);
        }

        $skPembinaan->delete();
    }

    public function importFromExcel(UploadedFile $file, ?int $createdBy): int
    {
        $import = new SkPembinaanImport($createdBy);

        Excel::import($import, $file);

        return $import->importedCount();
    }
}
