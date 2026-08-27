<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return Attendance::query()
            ->with('employee')
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('tanggal')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): Attendance
    {
        return Attendance::create($data)->fresh();
    }

    public function update(Attendance $attendance, array $data): Attendance
    {
        $attendance->update($data);

        return $attendance;
    }

    public function delete(Attendance $attendance): void
    {
        $attendance->delete();
    }
}
