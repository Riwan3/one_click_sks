<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\RosterEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RosterImport implements ToCollection, WithHeadingRow
{
    private int $imported = 0;

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

            $tanggal = $this->parseDate($row['tanggal'] ?? null);
            $kode = trim((string) ($row['kode'] ?? ''));

            if (! $tanggal || $kode === '') {
                continue;
            }

            RosterEntry::updateOrCreate(
                ['employee_id' => $employee->id, 'tanggal' => $tanggal],
                ['kode' => $kode, 'unit' => $row['unit'] ?? null]
            );

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
