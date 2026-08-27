<?php

namespace App\Imports;

use App\Models\AttendanceMonthlyRoster;
use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Excel import for the V1-parity monthly attendance roster.
 *
 * V1 behavior (ONECLICK V1 DashboardController::attendanceImport()):
 * - Template columns: NRP, Name, Dept, Jabatan, Posisi, Comp, ATR, then the
 *   15 status-code columns (D, N, IO, NC, L, T, O, S, I, A, K, P, R, H, NR).
 * - Month/year are NOT read from the spreadsheet — they come from a picker
 *   in the import UI and apply to every row in the batch. We follow the
 *   same approach: month/year are passed into the constructor.
 * - Employee identification is by NRP only; V1 does not auto-create
 *   employees during this import (unlike e.g. BeritaAcaraImport).
 * - Dedup/upsert: a row with the same NRP + month + year overwrites the
 *   existing record (no DB unique constraint in V1 — we enforce it here via
 *   the migration's unique index and use updateOrCreate to match the same
 *   upsert-on-conflict behavior).
 */
class AttendanceMonthlyRosterImport implements ToCollection, WithHeadingRow
{
    private int $imported = 0;

    private int $skipped = 0;

    public function __construct(
        private readonly int $bulan,
        private readonly int $tahun,
        private readonly ?int $createdBy,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $nrp = trim((string) ($row['nrp'] ?? ''));

            if ($nrp === '' || ! ctype_digit($nrp)) {
                $this->skipped++;

                continue;
            }

            $employee = Employee::where('nrp', $nrp)->first();

            if (! $employee) {
                $this->skipped++;

                continue;
            }

            AttendanceMonthlyRoster::updateOrCreate(
                ['employee_id' => $employee->id, 'bulan' => $this->bulan, 'tahun' => $this->tahun],
                $this->mapRow($row)
            );

            $this->imported++;
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

    private function mapRow(Collection $row): array
    {
        $codes = collect(AttendanceMonthlyRoster::CODE_COLUMNS)
            ->mapWithKeys(fn (string $code) => [$code => (int) ($row[$code] ?? 0)])
            ->all();

        $atr = trim((string) ($row['atr'] ?? ''));
        $atr = $atr === '' ? null : (float) str_replace('%', '', $atr);

        return array_merge($codes, [
            'dept' => $row['dept'] ?? null,
            'jabatan' => $row['jabatan'] ?? null,
            'posisi' => $row['posisi'] ?? null,
            'comp' => $row['comp'] ?? null,
            'atr_percentage' => $atr,
            'created_by' => $this->createdBy,
        ]);
    }
}
