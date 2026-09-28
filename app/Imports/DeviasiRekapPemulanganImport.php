<?php

namespace App\Imports;

use App\Models\DeviasiRekapPemulangan;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DeviasiRekapPemulanganImport implements ToCollection, WithHeadingRow
{
    private int $imported = 0;

    public function __construct(private readonly ?string $createdBy = null) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $nrp = trim((string) ($row['nrp'] ?? $row['nik'] ?? $row['badge'] ?? $row['no_badge'] ?? ''));

            if ($nrp === '' || strtolower($nrp) === 'nrp') {
                continue;
            }

            $nama = trim((string) ($row['nama'] ?? $row['nama_operator'] ?? $row['nama_karyawan'] ?? $row['name'] ?? ''));
            $jabatan = trim((string) ($row['jabatan'] ?? $row['position'] ?? $row['posisi'] ?? ''));

            if ($nama === '' || $jabatan === '') {
                $emp = Employee::where('nrp', $nrp)->first();
                if ($emp) {
                    if ($nama === '') {
                        $nama = $emp->name;
                    }
                    if ($jabatan === '') {
                        $jabatan = $emp->jabatan_ss6;
                    }
                }
            }

            if ($nama === '') {
                $nama = $nrp;
            }

            $tanggal = $this->parseDate($row['tanggal'] ?? $row['tgl'] ?? $row['date'] ?? $row['tgl_pulang'] ?? $row['tanggal_pulang'] ?? null) ?? now()->toDateString();

            $rawShift = strtoupper(trim((string) ($row['shift'] ?? $row['sift'] ?? 'D')));
            $shift = in_array($rawShift, ['D', 'N'], true) ? $rawShift : 'D';

            $rawJam = $row['jam_pulang'] ?? $row['jam'] ?? $row['waktu'] ?? $row['time'] ?? '08:00';
            $jam = $this->parseTime($rawJam) ?? '08:00:00';

            $keterangan = trim((string) ($row['keterangan'] ?? $row['alasan'] ?? $row['detail'] ?? $row['remarks'] ?? $row['note'] ?? $row['catatan'] ?? ''));
            $konfirmasi = trim((string) ($row['konfirmasi'] ?? $row['status_konfirmasi'] ?? $row['approved_by'] ?? $row['gl'] ?? $row['pengawas'] ?? ''));

            DeviasiRekapPemulangan::create([
                'nrp' => $nrp,
                'nama' => $nama,
                'jabatan' => $jabatan ?: null,
                'tanggal' => $tanggal,
                'shift' => $shift,
                'jam' => $jam,
                'alasan' => $keterangan,
                'keterangan' => $keterangan,
                'konfirmasi' => $konfirmasi ?: null,
                'created_by' => $this->createdBy,
            ]);

            $this->imported++;
        }
    }

    public function getImportedCount(): int
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
}
