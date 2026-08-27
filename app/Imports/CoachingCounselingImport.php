<?php

namespace App\Imports;

use App\Enums\EmployeeStatus;
use App\Models\CoachingCounseling;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CoachingCounselingImport implements ToCollection, WithHeadingRow
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

            $employee = Employee::firstOrCreate(
                ['nrp' => $nrp],
                [
                    'name' => $row['nama_karyawan'] ?? $nrp,
                    'jabatan_ss6' => $row['jabatan'] ?? null,
                    'status_karyawan' => EmployeeStatus::Inactive,
                ]
            );

            $tanggal = $this->parseDate($row['tanggal'] ?? null);

            if (! $tanggal) {
                continue;
            }

            CoachingCounseling::create([
                'nomor_surat' => CoachingCounseling::nextNomorSurat($tanggal),
                'employee_id' => $employee->id,
                'tanggal' => $tanggal,
                'lokasi' => $row['lokasi'] ?? null,
                'waktu_pertemuan' => $this->parseTime($row['waktu_pertemuan'] ?? null),
                'materi_pribadi' => $this->parseBool($row['materi_pribadi'] ?? null),
                'materi_keluarga' => $this->parseBool($row['materi_keluarga'] ?? null),
                'materi_lingkungan' => $this->parseBool($row['materi_lingkungan'] ?? null),
                'materi_pekerjaan' => $this->parseBool($row['materi_pekerjaan'] ?? null),
                'fatigue_level' => $this->parseFatigueLevel($row['fatigue_level'] ?? null),
                'deskripsi' => $row['deskripsi'] ?? null,
                'rekomendasi' => $row['rekomendasi'] ?? null,
                'coach_employee_id' => $this->resolveCoach($row['coach'] ?? null),
                'created_by' => $this->createdBy,
            ]);

            $this->imported++;
        }

        CoachingCounseling::resyncNomorSurat();
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
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseTime(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->format('H:i:s');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse((string) $value)->format('H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseBool(mixed $value): bool
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, ['1', 'ya', 'yes', 'true'], true);
    }

    private function parseFatigueLevel(mixed $value): int
    {
        $value = (int) trim((string) $value);

        return max(0, min(10, $value));
    }

    private function resolveCoach(mixed $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $employee = Employee::where('nrp', $value)->orWhere('name', $value)->first();

        return $employee?->id;
    }
}
