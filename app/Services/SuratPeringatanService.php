<?php

namespace App\Services;

use App\Imports\SuratPeringatanImport;
use App\Models\SuratPeringatan;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SuratPeringatanService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return SuratPeringatan::query()
            ->with(['employee', 'pengawas', 'admin', 'creator', 'updater'])
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('dikeluarkan_tanggal')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $adminId, ?int $createdBy): SuratPeringatan
    {
        $data['nomor_sp'] = SuratPeringatan::nextNumber($data['jenis_pembinaan'], $data['dikeluarkan_tanggal']);
        $data['admin_id'] = $adminId;
        $data['created_by'] = $createdBy;

        if (isset($data['foto']) && $data['foto'] instanceof UploadedFile) {
            $data['foto'] = $data['foto']->store('uploads/sp', 'public');
        } else {
            unset($data['foto']);
        }

        return SuratPeringatan::create($data);
    }

    public function update(SuratPeringatan $suratPeringatan, array $data, ?int $updatedBy): SuratPeringatan
    {
        $data['updated_by'] = $updatedBy;

        if (! empty($data['hapus_foto'])) {
            if ($suratPeringatan->foto) {
                Storage::disk('public')->delete($suratPeringatan->foto);
            }
            $data['foto'] = null;
        } elseif (isset($data['foto']) && $data['foto'] instanceof UploadedFile) {
            if ($suratPeringatan->foto) {
                Storage::disk('public')->delete($suratPeringatan->foto);
            }
            $data['foto'] = $data['foto']->store('uploads/sp', 'public');
        } else {
            unset($data['foto']);
        }
        unset($data['hapus_foto']);

        $suratPeringatan->update($data);

        return $suratPeringatan;
    }

    public function delete(SuratPeringatan $suratPeringatan): void
    {
        if ($suratPeringatan->foto) {
            Storage::disk('public')->delete($suratPeringatan->foto);
        }

        $suratPeringatan->delete();
    }

    public function activeSpFor(int $employeeId, ?string $tanggalRef): ?SuratPeringatan
    {
        return SuratPeringatan::activeFor($employeeId, $tanggalRef);
    }

    public function importFromExcel(UploadedFile $file, ?int $createdBy): int
    {
        $import = new SuratPeringatanImport($createdBy);

        Excel::import($import, $file);

        return $import->importedCount();
    }
}
