<?php

namespace App\Services;

use App\Models\ValidasiIstirahat;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ValidasiIstirahatService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return ValidasiIstirahat::query()
            ->with(['employee', 'creator', 'updater'])
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('tanggal_validasi')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): ValidasiIstirahat
    {
        [$docRank, $noSurat] = ValidasiIstirahat::nextDocRank($data['tanggal_validasi']);

        $data['doc_rank'] = $data['doc_rank'] ?? $docRank;
        $data['no_surat'] = $data['no_surat'] ?? $noSurat;
        $data['created_by'] = $createdBy;

        if (isset($data['foto']) && $data['foto'] instanceof UploadedFile) {
            $data['foto'] = $data['foto']->store('uploads/validasi_istirahat', 'public');
        }

        return ValidasiIstirahat::create($data)->fresh();
    }

    public function update(ValidasiIstirahat $validasiIstirahat, array $data, ?int $updatedBy = null): ValidasiIstirahat
    {
        if ($updatedBy) {
            $data['updated_by'] = $updatedBy;
        }

        if (! empty($data['hapus_foto']) && $validasiIstirahat->foto) {
            if (Storage::disk('public')->exists($validasiIstirahat->foto)) {
                Storage::disk('public')->delete($validasiIstirahat->foto);
            }
            $data['foto'] = null;
        } elseif (isset($data['foto']) && $data['foto'] instanceof UploadedFile) {
            if ($validasiIstirahat->foto && Storage::disk('public')->exists($validasiIstirahat->foto)) {
                Storage::disk('public')->delete($validasiIstirahat->foto);
            }
            $data['foto'] = $data['foto']->store('uploads/validasi_istirahat', 'public');
        } else {
            unset($data['foto']);
        }
        unset($data['hapus_foto']);

        $validasiIstirahat->update($data);

        return $validasiIstirahat;
    }

    public function delete(ValidasiIstirahat $validasiIstirahat): void
    {
        if ($validasiIstirahat->foto && Storage::disk('public')->exists($validasiIstirahat->foto)) {
            Storage::disk('public')->delete($validasiIstirahat->foto);
        }

        $validasiIstirahat->delete();
    }
}
