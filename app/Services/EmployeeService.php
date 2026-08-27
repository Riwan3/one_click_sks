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
