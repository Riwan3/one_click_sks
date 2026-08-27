<?php

namespace App\Services;

use App\Models\PemanggilanKaryawan;
use Illuminate\Pagination\LengthAwarePaginator;

class PemanggilanKaryawanService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return PemanggilanKaryawan::query()
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): PemanggilanKaryawan
    {
        $data['created_by'] = $createdBy;

        return PemanggilanKaryawan::create($data);
    }

    public function update(PemanggilanKaryawan $pemanggilan, array $data): PemanggilanKaryawan
    {
        $pemanggilan->update($data);

        return $pemanggilan;
    }

    public function delete(PemanggilanKaryawan $pemanggilan): void
    {
        $pemanggilan->delete();
    }
}
