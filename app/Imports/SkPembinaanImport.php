<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\SkPembinaan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SkPembinaanImport implements ToCollection, WithHeadingRow
{
    private int $imported = 0;

    public function __construct(private readonly ?int $createdBy) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $nrp = trim((string) ($row['nrp'] ?? ''));

            if ($nrp === '') {
                continue;
            }

            $employee = Employee::where('nrp', $nrp)->first();

            if (! $employee) {
                continue;
            }

            $tanggalPelanggaran = $this->parseDate($row['tanggal_pelanggaran'] ?? null);

            if (! $tanggalPelanggaran) {
                continue;
            }

            $dikeluarkanTanggal = $this->parseDate($row['dikeluarkan_tanggal'] ?? null) ?? $tanggalPelanggaran;
            $perusahaan = $row['perusahaan'] ?? 'PT. PUTRA PERKASA ABADI';
            $berlakuSampai = $this->parseDate($row['berlaku_sampai'] ?? null) ?? Carbon::parse($dikeluarkanTanggal)->addYear()->format('Y-m-d');

            SkPembinaan::create([
                'nomor_surat' => SkPembinaan::nextNumber($dikeluarkanTanggal, $perusahaan),
                'perusahaan' => $perusahaan,
                'employee_id' => $employee->id,
                'tanggal_pelanggaran' => $tanggalPelanggaran,
                'lokasi' => $row['lokasi'] ?? null,
                'berlaku_sampai' => $berlakuSampai,
                'spdk_ref' => $row['spdk_ref'] ?? null,
                'dikeluarkan_di' => $row['dikeluarkan_di'] ?? null,
                'dikeluarkan_tanggal' => $dikeluarkanTanggal,
                'jenis_pelanggaran' => $row['jenis_pelanggaran'] ?? null,
                'lubang' => (int) ($row['lubang'] ?? 0),
                'keterangan' => $row['keterangan'] ?? null,
                'created_by' => $this->createdBy,
            ]);

            $this->imported++;
        }
    }

    public function importedCount(): int
    {
        return $this->imported;
    }

    private function parseDate(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->format('Y-m-d');
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
