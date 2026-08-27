<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\HmData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class HmDataImport implements ToCollection, WithHeadingRow
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

            $tanggal = $this->parseDate($row['tanggal'] ?? null);

            if (! $tanggal) {
                continue;
            }

            $hmStart = (float) ($row['hm_start'] ?? 0);
            $hmStop = (float) ($row['hm_stop'] ?? 0);

            HmData::updateOrCreate(
                [
                    'tanggal' => $tanggal,
                    'shift' => (string) ($row['shift'] ?? '-'),
                    'employee_id' => $employee->id,
                    'unit' => (string) ($row['unit'] ?? '-'),
                ],
                [
                    'dept' => $row['dept'] ?? null,
                    'hm_start' => $hmStart,
                    'hm_stop' => $hmStop,
                    'hm_diff' => $row['hm_diff'] ?? max(0, $hmStop - $hmStart),
                    'activity' => $row['activity'] ?? null,
                    'parking' => $row['parking'] ?? null,
                    'created_by' => $this->createdBy,
                ]
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
