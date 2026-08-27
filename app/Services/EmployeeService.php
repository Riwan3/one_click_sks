<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;

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
}
