<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    public function paginate(?string $search, ?string $jabatan, bool $onlyInactive, int $page, int $perPage): LengthAwarePaginator
    {
        return Employee::query()
            ->search($search)
            ->jabatan($jabatan)
            ->when($onlyInactive, fn ($q) => $q->inactiveOnly(), fn ($q) => $q->activeOnly())
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function jabatanOptions(): array
    {
        return Employee::query()
            ->whereNotNull('jabatan_ss6')
            ->distinct()
            ->orderBy('jabatan_ss6')
            ->pluck('jabatan_ss6')
            ->all();
    }

    public function create(array $data): Employee
    {
        $role = Arr::pull($data, 'role');

        return DB::transaction(function () use ($data, $role) {
            $employee = Employee::create($data);
            $this->syncUser($employee, role: $role);

            return $employee;
        });
    }

    public function roleOptions(): array
    {
        return Role::query()->orderBy('label')->get(['code', 'label'])
            ->map(fn (Role $role) => ['value' => $role->code, 'label' => $role->label])
            ->all();
    }

    /**
     * Buat/perbarui akun login karyawan (username = NRP). Akun baru: role dari jabatan, password awal = NRP.
     */
    public function syncUser(Employee $employee, ?string $password = null, ?User $existingUser = null, ?string $role = null): User
    {
        static $passwordHashCache = [];

        $user = $existingUser ?: User::firstOrNew(['username' => $employee->nrp]);

        $user->name = $employee->name;
        $user->email = $this->isEmailTakenByOtherUser($employee) ? null : $employee->email;
        $user->jabatan = $employee->jabatan_ss6;
        $user->unit = $employee->unit;
        $user->lokasi = $employee->lokasi_kerja;
        $user->employee_id = $employee->id;

        if (! $user->exists) {
            $rawPass = $password ?: $employee->nrp;
            // Cache hashing & rounds=8 untuk mempercepat bulk import
            $user->role = $role ?: UserRole::fromJabatan($employee->jabatan_ss6)->value;
            $user->password = $passwordHashCache[$rawPass] ??= Hash::make($rawPass, ['rounds' => 8]);
        }

        $user->save();

        return $user;
    }

    private function isEmailTakenByOtherUser(Employee $employee): bool
    {
        return filled($employee->email)
            && User::where('email', $employee->email)->where('username', '!=', $employee->nrp)->exists();
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);

        return $employee;
    }

    public function delete(Employee $employee): void
    {
        DB::transaction(function () use ($employee) {
            User::where('employee_id', $employee->id)->delete();
            $employee->delete();
        });
    }

    public function setStatus(Employee $employee, string $status): Employee
    {
        $employee->update(['status_karyawan' => $status]);

        return $employee;
    }

    public function deleteAllInactive(): int
    {
        return DB::transaction(function () {
            User::whereIn('employee_id', Employee::query()->inactiveOnly()->select('id'))->delete();

            return Employee::query()->inactiveOnly()->delete();
        });
    }

    public function deleteAll(): int
    {
        return DB::transaction(function () {
            User::whereNotNull('employee_id')->delete();

            return Employee::query()->delete();
        });
    }

    /**
     * Employees for export, respecting the same search/jabatan/active-inactive
     * filters as the list view so exporting from a filtered page only exports
     * what's shown (mirrors V1's export-follows-current-view behaviour).
     */
    public function listAllGL(?string $search = null, ?int $restrictGlId = null): Collection
    {
        return Employee::query()
            ->activeOnly()
            ->when($restrictGlId, function ($q) use ($restrictGlId) {
                $q->where('id', $restrictGlId);
            }, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('jabatan_ss6', 'like', '%GROUP LEADER%')
                        ->orWhere('jabatan_ss6', 'like', '%GL%')
                        ->orWhereHas('anakAsuh');
                });
            })
            ->search($search)
            ->withCount('anakAsuh')
            ->orderBy('name')
            ->get();
    }

    public function getAnakAsuh(Employee $gl, ?string $search = null): Collection
    {
        return Employee::query()
            ->where('gl_id', $gl->id)
            ->search($search)
            ->orderBy('name')
            ->get();
    }

    public function getUnassignedEmployees(?string $search = null): Collection
    {
        return Employee::query()
            ->activeOnly()
            ->whereNull('gl_id')
            ->where('jabatan_ss6', 'not like', '%GROUP LEADER%')
            ->where('jabatan_ss6', 'not like', '%GL%')
            ->search($search)
            ->orderBy('name')
            ->get();
    }

    public function bulkAssignAnakAsuh(Employee $gl, array $employeeIds): int
    {
        if (empty($employeeIds)) {
            return 0;
        }

        return Employee::whereIn('id', $employeeIds)->update(['gl_id' => $gl->id]);
    }

    public function setGroupLeader(Employee $employee, ?int $glId): Employee
    {
        $employee->update(['gl_id' => $glId]);

        return $employee;
    }

    public function removeAsuh(Employee $employee): Employee
    {
        $employee->update(['gl_id' => null]);

        return $employee;
    }

    public function importSahabatPengusaha(\Illuminate\Http\UploadedFile $file, bool $reset = false): array
    {
        if ($reset) {
            Employee::query()->update(['gl_id' => null]);
        }

        $array = \Maatwebsite\Excel\Facades\Excel::toArray([], $file);
        $rows = $array[0] ?? [];

        $updated = 0;
        $skipped = 0;

        $v1GlMap = [];
        try {
            $v1GlRows = \Illuminate\Support\Facades\DB::select('SELECT id, nrp FROM oneclick.employees');
            foreach ($v1GlRows as $r) {
                $v1GlMap[(string) $r->id] = (string) $r->nrp;
            }
        } catch (\Throwable $e) {}

        $anakColIndex = 1;
        $bapakColIndex = 11;

        if (! empty($rows[0])) {
            $headerRow = array_map(fn ($h) => strtoupper(trim((string) $h)), $rows[0]);
            foreach ($headerRow as $idx => $headerName) {
                if (in_array($headerName, ['NRP', 'NRP_ANAK_ASUH', 'NRP ANAK ASUH', 'ANAK ASUH', 'ANAK_ASUH'], true)) {
                    $anakColIndex = $idx;
                } elseif (in_array($headerName, ['GL_ID', 'GL ID', 'ID_GL', 'ID GL', 'GL', 'GROUP LEADER', 'BAPAK ASUH', 'BAPAK_ASUH', 'NRP BAPAK ASUH'], true)) {
                    $bapakColIndex = $idx;
                }
            }
        }

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            } // Skip header row

            $anakVal = trim((string) ($row[$anakColIndex] ?? ''));
            $bapakVal = trim((string) ($row[$bapakColIndex] ?? ($bapakColIndex === 11 ? ($row[10] ?? '') : '')));

            if ($anakVal === '' || $bapakVal === '') {
                $skipped++;

                continue;
            }

            $anak = Employee::where('nrp', $anakVal)->orWhere('name', 'like', "%{$anakVal}%")->first();
            
            $targetGlId = null;
            if (is_numeric($bapakVal)) {
                $glInt = (int) $bapakVal;
                if (Employee::where('id', $glInt)->exists()) {
                    $targetGlId = $glInt;
                } elseif (isset($v1GlMap[(string) $bapakVal])) {
                    $glNrp = $v1GlMap[(string) $bapakVal];
                    $targetGl = Employee::where('nrp', $glNrp)->first();
                    $targetGlId = $targetGl?->id;
                }
            }

            if (! $targetGlId) {
                $bapak = Employee::where('nrp', $bapakVal)->orWhere('name', 'like', "%{$bapakVal}%")->first();
                $targetGlId = $bapak?->id;
            }

            if ($anak && $targetGlId) {
                $anak->update(['gl_id' => $targetGlId]);
                $updated++;
            } else {
                $skipped++;
            }
        }

        return [
            'updated' => $updated,
            'skipped' => $skipped,
            'message' => "Impor Sahabat Pengusaha selesai: {$updated} anak asuh berhasil ditautkan, {$skipped} dilewati.",
        ];
    }

    public function forExport(?string $search, ?string $jabatan, bool $onlyInactive): Collection
    {
        return Employee::query()
            ->search($search)
            ->jabatan($jabatan)
            ->when($onlyInactive, fn ($q) => $q->inactiveOnly(), fn ($q) => $q->activeOnly())
            ->orderBy('name')
            ->get();
    }
}
