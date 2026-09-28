<?php

namespace App\Services;

use App\Imports\SuratTeguranImport;
use App\Models\SuratTeguran;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SuratTeguranService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return SuratTeguran::query()
            ->with(['employee', 'creator', 'updater'])
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('dikeluarkan_tanggal')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): SuratTeguran
    {
        [$docRank, $noSurat] = SuratTeguran::nextDocRank($data['dikeluarkan_tanggal']);

        $data['doc_rank'] = $docRank;
        $data['no_surat'] = $noSurat;
        $data['created_by'] = $createdBy;

        if (isset($data['foto']) && $data['foto'] instanceof UploadedFile) {
            $data['foto'] = $data['foto']->store('uploads/surat_teguran', 'public');
        } else {
            unset($data['foto']);
        }

        return SuratTeguran::create($data);
    }

    public function update(SuratTeguran $suratTeguran, array $data, ?int $updatedBy): SuratTeguran
    {
        $data['updated_by'] = $updatedBy;

        if (! empty($data['hapus_foto'])) {
            if ($suratTeguran->foto) {
                Storage::disk('public')->delete($suratTeguran->foto);
            }
            $data['foto'] = null;
        } elseif (isset($data['foto']) && $data['foto'] instanceof UploadedFile) {
            if ($suratTeguran->foto) {
                Storage::disk('public')->delete($suratTeguran->foto);
            }
            $data['foto'] = $data['foto']->store('uploads/surat_teguran', 'public');
        } else {
            unset($data['foto']);
        }
        unset($data['hapus_foto']);

        $suratTeguran->update($data);

        return $suratTeguran;
    }

    public function delete(SuratTeguran $suratTeguran): void
    {
        if ($suratTeguran->foto) {
            Storage::disk('public')->delete($suratTeguran->foto);
        }

        $suratTeguran->delete();
    }

    public function importFromExcel(UploadedFile $file, ?int $createdBy): int
    {
        $import = new SuratTeguranImport($createdBy);

        Excel::import($import, $file);

        return $import->importedCount();
    }
}
