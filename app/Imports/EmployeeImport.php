<?php

namespace App\Imports;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class EmployeeImport implements ToCollection
{
    private int $created = 0;

    private int $updated = 0;

    /**
     * Maps recognized header labels (uppercased) to the Employee column they feed.
     * Mirrors V1's mapImportColumns()/extractImportRow() so the same template works here.
     */
    private const HEADER_MAP = [
        'NAMA' => 'name',
        'NAME' => 'name',
        'JABATAN SS6' => 'jabatan_ss6',
        'JABATAN' => 'jabatan_roster',
        'STATUS KARYAWAN' => 'status_karyawan',
        'STATUS' => 'status_karyawan',
        'LOKASI KERJA' => 'lokasi_kerja',
        'LOKASI' => 'lokasi_kerja',
        'UNIT' => 'unit',
        'NO TELEPON' => 'no_telepon',
        'TELEPON' => 'no_telepon',
        'INPUT HERE' => 'input_here',
        'INPUT HERE!!!' => 'input_here',
        'EMAIL' => 'email',
    ];

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $headerRow = $rows->first();
        $colMap = $this->mapColumns($headerRow);

        if (! isset($colMap['nrp'])) {
            return;
        }

        foreach ($rows->slice(1) as $row) {
            $this->importRow($row, $colMap);
        }
    }

    public function createdCount(): int
    {
        return $this->created;
    }

    public function updatedCount(): int
    {
        return $this->updated;
    }

    private function importRow(Collection $row, array $colMap): void
    {
        $nrp = trim((string) ($row[$colMap['nrp']] ?? ''));

        if ($nrp === '' || ! preg_match('/^\d+$/', $nrp)) {
            return;
        }

        $data = $this->extractRow($row, $colMap);
        $employee = Employee::where('nrp', $nrp)->first();

        if ($employee) {
            $employee->update($data);
            $this->updated++;

            return;
        }

        $defaults = [
            'name' => '',
            'jabatan_ss6' => null,
            'jabatan_roster' => null,
            'status_karyawan' => 'FIT',
            'lokasi_kerja' => null,
            'unit' => null,
            'no_telepon' => null,
            'input_here' => null,
            'email' => null,
            'efektif_bekerja' => null,
        ];

        Employee::create(array_merge($defaults, $data, ['nrp' => $nrp]));
        $this->created++;
    }

    /**
     * Only include fields whose column actually exists in the file, so updating
     * an employee from a partial file never blanks out fields that were absent.
     */
    private function extractRow(Collection $row, array $colMap): array
    {
        $data = [];

        foreach ($colMap as $field => $index) {
            if ($field === 'nrp' || $field === 'efektif_bekerja') {
                continue;
            }

            $value = trim((string) ($row[$index] ?? ''));
            $data[$field] = $value !== '' ? $value : null;
        }

        if (isset($colMap['efektif_bekerja'])) {
            $data['efektif_bekerja'] = $this->parseDate($row[$colMap['efektif_bekerja']] ?? null);
        }

        return $data;
    }

    /**
     * Map header labels (any order) to their zero-based column index, same rule
     * set as V1: exact matches for known labels, substring match for "EFEKTIF*".
     */
    private function mapColumns(Collection $headerRow): array
    {
        $colMap = [];

        foreach ($headerRow as $index => $cell) {
            $label = strtoupper(trim((string) $cell));

            if ($label === 'NRP') {
                $colMap['nrp'] = $index;

                continue;
            }

            if (str_contains($label, 'EFEKTIF')) {
                $colMap['efektif_bekerja'] = $index;

                continue;
            }

            if (isset(self::HEADER_MAP[$label])) {
                $colMap[self::HEADER_MAP[$label]] = $index;
            }
        }

        return $colMap;
    }

    private function parseDate(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value))->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)?->format('Y-m-d');
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
