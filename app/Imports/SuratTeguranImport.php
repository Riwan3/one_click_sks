<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\SuratTeguran;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SuratTeguranImport implements ToCollection, WithHeadingRow
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

            [$docRank, $noSurat] = SuratTeguran::nextDocRank($dikeluarkanTanggal);

            SuratTeguran::create([
                'doc_rank' => $docRank,
                'no_surat' => $noSurat,
                'employee_id' => $employee->id,
                'nrp' => $employee->nrp,
                'nama' => $employee->name,
                'jabatan' => $employee->jabatan_ss6,
                'departemen' => $employee->unit,
                'tanggal_kejadian' => $tanggalKejadian,
                'tempat_kejadian' => $row['tempat_kejadian'] ?? null,
                'jenis_absen' => $this->parseBool($row['jenis_absen'] ?? null),
                'jenis_perilaku' => $this->parseBool($row['jenis_perilaku'] ?? null),
                'jenis_ceroboh' => $this->parseBool($row['jenis_ceroboh'] ?? null),
                'jenis_hasil_kerja' => $this->parseBool($row['jenis_hasil_kerja'] ?? null),
                'jenis_menolak_perintah' => $this->parseBool($row['jenis_menolak_perintah'] ?? null),
                'jenis_pelanggaran_peraturan' => $this->parseBool($row['jenis_pelanggaran_peraturan'] ?? null),
                'penjelasan_singkat' => $row['penjelasan_singkat'] ?? null,
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
}
