<?php

namespace App\Imports;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use App\Models\PaktaIntegritas;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PaktaIntegritasImport implements ToCollection, WithHeadingRow
{
    private int $imported = 0;

    private int $invalidDates = 0;

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
                $tanggal = now()->toDateString();
                $this->invalidDates++;
            }

            $atasanId = $this->resolveEmployee($row['atasan_nama'] ?? null);
            $pimpinanId = $this->resolveEmployee($row['pimpinan_nama'] ?? null);

            PaktaIntegritas::create([
                'nomor_surat' => PaktaIntegritas::nextNomorSurat($tanggal),
                'employee_id' => $employee->id,
                'tanggal' => $tanggal,
                'tempat' => $row['tempat'] ?? '',
                'pernyataan' => $row['pernyataan'] ?? $this->defaultPernyataan(),
                'atasan_nama' => $row['atasan_nama'] ?? '',
                'mengetahui_nama' => $row['pimpinan_nama'] ?? '',
                'cc' => $row['cc'] ?? 'Bapak Fachrul Latuconsina',
                'arsip' => $row['arsip'] ?? 'Dokumen Control MPE',
                'created_by' => $this->createdBy,
            ]);

            $this->imported++;
        }

        PaktaIntegritas::resyncNomorSurat();
    }

    public function importedCount(): int
    {
        return $this->imported;
    }

    public function invalidDateCount(): int
    {
        return $this->invalidDates;
    }

    private function resolveEmployee(mixed $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $employee = Employee::where('nrp', $value)->orWhere('name', $value)->first();

        return $employee?->id;
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

    private function defaultPernyataan(): string
    {
        return 'Saya berkomitmen dengan bersungguh-sungguh untuk menjaga ATR dan Kinerja sesuai dengan standar perusahaan.';
    }
}
