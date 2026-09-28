<?php

namespace App\Services;

use App\Imports\BeritaAcaraImport;
use App\Models\BeritaAcara;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class BeritaAcaraService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return BeritaAcara::query()
            ->with(['employee', 'creator', 'diketahuiOleh'])
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): BeritaAcara
    {
        if (empty($data['nomor_surat'])) {
            $data['nomor_surat'] = BeritaAcara::nextNomorSurat($data['tanggal']);
        }
        $data['no_dokumen'] = !empty($data['no_dokumen']) ? $data['no_dokumen'] : 'PPA-ADRO-F-SHE-24D';
        $data['revisi'] = isset($data['revisi']) && $data['revisi'] !== '' ? $data['revisi'] : '0';
        $data['tgl_efektif'] = !empty($data['tgl_efektif']) ? $data['tgl_efektif'] : '2022-04-07';
        $data['created_by'] = $createdBy;

        $beritaAcara = BeritaAcara::create($data)->fresh();

        return $beritaAcara;
    }

    public function update(BeritaAcara $beritaAcara, array $data, ?int $updatedBy): BeritaAcara
    {
        $data['updated_by'] = $updatedBy;
        $beritaAcara->update($data);

        return $beritaAcara->fresh();
    }

    public function delete(BeritaAcara $beritaAcara): void
    {
        $beritaAcara->delete();

        BeritaAcara::resyncNomorSurat();
    }

    public function importFromExcel(UploadedFile $file, ?int $createdBy): array
    {
        $import = new BeritaAcaraImport($createdBy);

        Excel::import($import, $file);

        return [
            'imported' => $import->importedCount(),
            'invalidDates' => $import->invalidDateCount(),
        ];
    }
}
