<?php

namespace App\Services;

use App\Imports\PaktaIntegritasImport;
use App\Models\PaktaIntegritas;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PaktaIntegritasService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return PaktaIntegritas::query()
            ->with(['employee', 'creator'])
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): PaktaIntegritas
    {
        $data['nomor_surat'] = PaktaIntegritas::nextNomorSurat($data['tanggal']);
        $data['created_by'] = $createdBy;

        if (isset($data['foto']) && $data['foto'] instanceof UploadedFile) {
            $data['foto'] = $data['foto']->store('uploads/pakta_integritas', 'public');
        } else {
            unset($data['foto']);
        }

        return PaktaIntegritas::create($data)->fresh();
    }

    public function update(PaktaIntegritas $paktaIntegritas, array $data): PaktaIntegritas
    {
        if (! empty($data['hapus_foto'])) {
            if ($paktaIntegritas->foto) {
                Storage::disk('public')->delete($paktaIntegritas->foto);
            }
            $data['foto'] = null;
        } elseif (isset($data['foto']) && $data['foto'] instanceof UploadedFile) {
            if ($paktaIntegritas->foto) {
                Storage::disk('public')->delete($paktaIntegritas->foto);
            }
            $data['foto'] = $data['foto']->store('uploads/pakta_integritas', 'public');
        } else {
            unset($data['foto']);
        }
        unset($data['hapus_foto']);

        $paktaIntegritas->update($data);

        return $paktaIntegritas;
    }

    public function delete(PaktaIntegritas $paktaIntegritas): void
    {
        if ($paktaIntegritas->foto) {
            Storage::disk('public')->delete($paktaIntegritas->foto);
        }

        $paktaIntegritas->delete();
    }

    public function importFromExcel(UploadedFile $file, ?int $createdBy): int
    {
        $import = new PaktaIntegritasImport($createdBy);
        Excel::import($import, $file);

        return $import->importedCount();
    }
}
