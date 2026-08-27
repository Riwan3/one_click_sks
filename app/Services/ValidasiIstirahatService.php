<?php

namespace App\Services;

use App\Models\ValidasiIstirahat;
use Illuminate\Pagination\LengthAwarePaginator;

class ValidasiIstirahatService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return ValidasiIstirahat::query()
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): ValidasiIstirahat
    {
        [$docRank, $noSurat] = ValidasiIstirahat::nextDocRank($data['tanggal_validasi']);

        $data['doc_rank'] = $docRank;
        $data['no_surat'] = $noSurat;
        $data['created_by'] = $createdBy;

        return ValidasiIstirahat::create($data)->fresh();
    }

    public function update(ValidasiIstirahat $validasiIstirahat, array $data, ?int $updatedBy): ValidasiIstirahat
    {
        $data['updated_by'] = $updatedBy;
        $validasiIstirahat->update($data);

        return $validasiIstirahat;
    }

    public function delete(ValidasiIstirahat $validasiIstirahat): void
    {
        $validasiIstirahat->delete();
    }
}
