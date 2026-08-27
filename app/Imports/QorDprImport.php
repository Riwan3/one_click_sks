<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\QorDpr;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import DPR (Daily Production Report) rows into qor_dpr.
 *
 * Mengikuti pola V1: satu baris file bisa menghasilkan hingga 2 record qor_dpr
 * (operator excavator/loading via kolom "operator_ex", operator hauling dump
 * truck via kolom "operator_hd") kalau NRP keduanya berbeda.
 */
class QorDprImport implements ToCollection, WithHeadingRow
{
    private int $imported = 0;

    private int $skipped = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $tanggal = $this->parseDate($row['tanggal'] ?? null);

            if (! $tanggal) {
                $this->skipped++;

                continue;
            }

            $shared = [
                'tanggal' => $tanggal,
                'loader' => $this->nullableString($row['loader'] ?? null),
                'jarak' => $this->parseJarak($row['jarak'] ?? null),
                'kode_unit' => $this->nullableString($row['kode_unit'] ?? null),
                'rit' => $this->parseFloat($row['rit'] ?? null),
                'total_bcm' => $this->parseFloat($row['total_bcm'] ?? $row['bcm'] ?? null),
            ];

            $nrpEx = $this->cleanNrp($row['operator_ex'] ?? null);
            $nrpHd = $this->cleanNrp($row['operator_hd'] ?? null);

            $this->storeRow($nrpEx, 'EXCA', $shared);

            if ($nrpHd !== '' && $nrpHd !== $nrpEx) {
                $this->storeRow($nrpHd, 'HD', $shared);
            }
        }
    }

    public function importedCount(): int
    {
        return $this->imported;
    }

    public function skippedCount(): int
    {
        return $this->skipped;
    }

    private function storeRow(string $nrp, string $roleOp, array $shared): void
    {
        if ($nrp === '') {
            $this->skipped++;

            return;
        }

        $employee = Employee::firstOrCreate(
            ['nrp' => $nrp],
            ['name' => $nrp]
        );

        QorDpr::create([
            'employee_id' => $employee->id,
            'role_op' => $roleOp,
            ...$shared,
        ]);

        $this->imported++;
    }

    private function cleanNrp(mixed $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        // Normalisasi NRP numerik dari notasi ilmiah Excel (mis. "1.234E+11").
        if (is_numeric($value)) {
            return (string) (int) $value;
        }

        return $value;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function parseFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = str_replace(',', '.', (string) $value);

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    private function parseJarak(mixed $value): ?float
    {
        $value = $this->parseFloat($value);

        if ($value === null) {
            return null;
        }

        // Ikuti V1: auto-convert meter ke km kalau nilainya tampak seperti meter.
        if ($value > 50) {
            $value /= 1000;
        }

        return round($value, 3);
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
