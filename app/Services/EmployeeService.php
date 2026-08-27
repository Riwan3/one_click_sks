<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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
        return Employee::create($data);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);

        return $employee;
    }

    public function delete(Employee $employee): void
    {
        $employee->delete();
    }

    public function setStatus(Employee $employee, string $status): Employee
    {
        $employee->update(['status_karyawan' => $status]);

        return $employee;
    }

    public function deleteAllInactive(): int
    {
        return Employee::query()->inactiveOnly()->delete();
    }

    /**
     * Employees for export, respecting the same search/jabatan/active-inactive
     * filters as the list view so exporting from a filtered page only exports
     * what's shown (mirrors V1's export-follows-current-view behaviour).
     */
    public function listAllGL(?string $search = null): Collection
    {
        return Employee::query()
            ->activeOnly()
            ->where(function ($q) {
                $q->where('jabatan_ss6', 'like', '%GROUP LEADER%')
                    ->orWhere('jabatan_ss6', 'like', '%GL%')
                    ->orWhereHas('anakAsuh');
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

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            } // Skip header row

            // Column B (index 1) = Anak Asuh (NRP/Name), Column L (index 11) = Bapak Asuh (NRP/Name)
            $anakVal = trim((string) ($row[1] ?? ''));
            $bapakVal = trim((string) ($row[11] ?? $row[10] ?? ''));

            if ($anakVal === '' || $bapakVal === '') {
                $skipped++;

                continue;
            }

            $anak = Employee::where('nrp', $anakVal)->orWhere('name', 'like', "%{$anakVal}%")->first();
            $bapak = Employee::where('nrp', $bapakVal)->orWhere('name', 'like', "%{$bapakVal}%")->first();

            if ($anak && $bapak) {
                $anak->update(['gl_id' => $bapak->id]);
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
