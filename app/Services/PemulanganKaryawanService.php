<?php

namespace App\Services;

use App\Models\PemulanganKaryawan;
use Illuminate\Pagination\LengthAwarePaginator;

class PemulanganKaryawanService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return PemulanganKaryawan::query()
            ->with(['employee', 'creator', 'updater'])
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): PemulanganKaryawan
    {
        [$docRank, $noSurat] = PemulanganKaryawan::nextDocRank($data['tanggal']);

        $data['doc_rank'] = $data['doc_rank'] ?? $docRank;
        $data['no_surat'] = $data['no_surat'] ?? $noSurat;
        $data['created_by'] = $createdBy;

        return PemulanganKaryawan::create($data);
    }

    public function update(PemulanganKaryawan $pemulangan, array $data, ?int $updatedBy = null): PemulanganKaryawan
    {
        if ($updatedBy) {
            $data['updated_by'] = $updatedBy;
        }

        $pemulangan->update($data);

        return $pemulangan;
    }

    public function delete(PemulanganKaryawan $pemulangan): void
    {
        $pemulangan->delete();
    }
}
