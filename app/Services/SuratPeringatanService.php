<?php

namespace App\Services;

use App\Imports\SuratPeringatanImport;
use App\Models\SuratPeringatan;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class SuratPeringatanService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return SuratPeringatan::query()
            ->with(['employee', 'pengawas', 'admin'])
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

        return SuratPeringatan::create($data);
    }

    public function update(SuratPeringatan $suratPeringatan, array $data, ?int $updatedBy): SuratPeringatan
    {
        $data['updated_by'] = $updatedBy;
        $suratPeringatan->update($data);

        return $suratPeringatan;
    }

    public function delete(SuratPeringatan $suratPeringatan): void
    {
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
