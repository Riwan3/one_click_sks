<?php

namespace App\Imports;

use App\Enums\JenisPembinaan;
use App\Models\Employee;
use App\Models\SuratPeringatan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SuratPeringatanImport implements ToCollection, WithHeadingRow
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

            $tanggalKejadian = $this->parseDate($row['tanggal_kejadian'] ?? null);

            if (! $tanggalKejadian) {
                continue;
            }

            $dikeluarkanTanggal = $this->parseDate($row['dikeluarkan_tanggal'] ?? null) ?? $tanggalKejadian;
            $jenisPembinaan = $this->parseJenis($row['jenis_pembinaan'] ?? null);
            $nomorSp = SuratPeringatan::nextNumber($jenisPembinaan->value, $dikeluarkanTanggal);

            SuratPeringatan::create([
                'employee_id' => $employee->id,
                'nomor_sp' => $nomorSp,
                'kode_pelanggaran' => $row['kode_pelanggaran'] ?? null,
                'jenis_pembinaan' => $jenisPembinaan,
                'detail_pelanggaran' => $row['detail_pelanggaran'] ?? null,
                'tanggal_kejadian' => $tanggalKejadian,
                'tempat_kejadian' => $row['tempat_kejadian'] ?? null,
                'pelanggaran_absen_terlambat' => $this->parseBool($row['pelanggaran_absen_terlambat'] ?? null),
                'pelanggaran_perilaku' => $this->parseBool($row['pelanggaran_perilaku'] ?? null),
                'pelanggaran_ceroboh_lalai' => $this->parseBool($row['pelanggaran_ceroboh_lalai'] ?? null),
                'pelanggaran_hasil_kerja' => $this->parseBool($row['pelanggaran_hasil_kerja'] ?? null),
                'pelanggaran_menolak_perintah' => $this->parseBool($row['pelanggaran_menolak_perintah'] ?? null),
                'pelanggaran_peraturan' => $this->parseBool($row['pelanggaran_peraturan'] ?? null),
                'sanksi_masa_berlaku' => (int) ($row['sanksi_masa_berlaku'] ?? 6),
                'dikeluarkan_di' => $row['dikeluarkan_di'] ?? null,
                'dikeluarkan_tanggal' => $dikeluarkanTanggal,
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

    private function parseBool(mixed $value): bool
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, ['1', 'ya', 'yes', 'true'], true);
    }

    private function parseJenis(mixed $value): JenisPembinaan
    {
        $value = trim((string) $value);

        return match (true) {
            str_contains(strtolower($value), 'kedua') => JenisPembinaan::Kedua,
            str_contains(strtolower($value), 'terakhir') => JenisPembinaan::Terakhir,
            default => JenisPembinaan::Pertama,
        };
    }
}
