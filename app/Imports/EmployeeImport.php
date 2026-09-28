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
        'PASSWORD' => 'password',
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
        'GL_ID' => 'gl_id',
        'GL ID' => 'gl_id',
        'ID GL' => 'gl_id',
    ];

    private array $pendingGlAssignments = [];

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

        $this->pendingGlAssignments = [];

        // 1. Preload data yang sudah ada ke memory agar tidak melakukan ribuan query individual (jauh lebih cepat)
        $existingEmployees = Employee::all()->keyBy('nrp');
        $existingUsers = \App\Models\User::all()->keyBy('username');

        // 2. Preload mapping ID dari tabel v1 (oneclick.employees) jika ada (agar ID lama seperti 1507/1795 otomatis terpetakan)
        $v1GlMap = [];
        try {
            $v1GlRows = \Illuminate\Support\Facades\DB::select('SELECT id, nrp FROM oneclick.employees');
            foreach ($v1GlRows as $r) {
                $v1GlMap[(string) $r->id] = (string) $r->nrp;
            }
        } catch (\Throwable $e) {
            // Abaikan jika database oneclick tidak ada
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($rows, $colMap, $existingEmployees, $existingUsers, $v1GlMap) {
            // Pass 1: Import & perbarui data profil karyawan
            foreach ($rows->slice(1) as $row) {
                $this->importRow($row, $colMap, $existingEmployees, $existingUsers);
            }

            // Pass 2: Kaitkan gl_id setelah seluruh data karyawan selesai dimasukkan
            $allEmployeesByNrp = Employee::pluck('id', 'nrp');
            $allEmployeesById = Employee::pluck('id', 'id');

            foreach ($this->pendingGlAssignments as $pending) {
                $rawGl = (string) $pending['gl_val'];
                if ($rawGl === '') {
                    continue;
                }

                $glId = null;

                // Skenario A: Langsung cocok dengan ID di database saat ini
                if (isset($allEmployeesById[(int) $rawGl])) {
                    $glId = (int) $rawGl;
                }
                // Skenario B: Merupakan ID lama dari database v1 (cth: 1507 atau 1795) -> dipetakan via NRP
                elseif (isset($v1GlMap[$rawGl])) {
                    $glNrp = $v1GlMap[$rawGl];
                    $glId = $allEmployeesByNrp->get($glNrp);
                }
                // Skenario C: Diisi NRP Group Leader langsung
                elseif ($allEmployeesByNrp->has($rawGl)) {
                    $glId = $allEmployeesByNrp->get($rawGl);
                }

                if ($glId) {
                    Employee::where('nrp', $pending['employee_nrp'])->update(['gl_id' => $glId]);
                }
            }
        });
    }

    public function createdCount(): int
    {
        return $this->created;
    }

    public function updatedCount(): int
    {
        return $this->updated;
    }

    private function importRow(Collection $row, array $colMap, $existingEmployees, $existingUsers): void
    {
        $nrp = trim((string) ($row[$colMap['nrp']] ?? ''));

        if ($nrp === '' || ! preg_match('/^\d+$/', $nrp)) {
            return;
        }

        $data = $this->extractRow($row, $colMap);
        $password = $data['password'] ?? $nrp;
        unset($data['password']); // Prevent inserting to employee table

        // Tunda update gl_id ke pass 2 agar tidak terkena Foreign Key Constraint Violation
        if (array_key_exists('gl_id', $data)) {
            $rawGl = $data['gl_id'];
            unset($data['gl_id']);
            if ($rawGl !== null && $rawGl !== '') {
                $this->pendingGlAssignments[] = [
                    'employee_nrp' => $nrp,
                    'gl_val' => $rawGl,
                ];
            }
        }

        $employee = $existingEmployees->get($nrp);

        if ($employee) {
            $employee->update($data);
            $this->syncUser($employee, $password, $existingUsers->get($nrp));
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

        $employee = Employee::create(array_merge($defaults, $data, ['nrp' => $nrp]));
        $existingEmployees->put($nrp, $employee);
        $this->syncUser($employee, $password, null);
        $this->created++;
    }

    private function syncUser(Employee $employee, string $password, ?\App\Models\User $existingUser): void
    {
        static $passwordHashCache = [];

        $user = $existingUser ?: new \App\Models\User(['username' => $employee->nrp]);

        $user->name = $employee->name;
        $user->email = $employee->email;
        $user->jabatan = $employee->jabatan_ss6;
        $user->unit = $employee->unit;
        $user->lokasi = $employee->lokasi_kerja;
        $user->employee_id = $employee->id;

        if (! $user->exists) {
            $user->role = \App\Enums\UserRole::fromJabatan($employee->jabatan_ss6)->value;
            $rawPass = $password ?: $employee->nrp;
            // Cache hashing & gunakan rounds=8 untuk mempercepat proses bulk import
            $user->password = $passwordHashCache[$rawPass] ??= \Illuminate\Support\Facades\Hash::make($rawPass, ['rounds' => 8]);
        }

        $user->save();
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
