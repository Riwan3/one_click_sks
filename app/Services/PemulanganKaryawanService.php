<?php

namespace App\Services;

use App\Models\PemulanganKaryawan;
use Illuminate\Pagination\LengthAwarePaginator;

class PemulanganKaryawanService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return PemulanganKaryawan::query()
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): PemulanganKaryawan
    {
        [$docRank, $noSurat] = PemulanganKaryawan::nextDocRank($data['tanggal']);

        $data['doc_rank'] = $docRank;
        $data['no_surat'] = $noSurat;
        $data['created_by'] = $createdBy;

        return PemulanganKaryawan::create($data);
    }

    public function update(PemulanganKaryawan $pemulangan, array $data): PemulanganKaryawan
    {
        $pemulangan->update($data);

        return $pemulangan;
    }

    public function delete(PemulanganKaryawan $pemulangan): void
    {
        $pemulangan->delete();
    }
}
