<?php

namespace App\Services;

use App\Imports\RosterImport;
use App\Models\Employee;
use App\Models\RosterEntry;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class RosterService
{
    /**
     * Label keterangan per kode roster, dipakai kalender Roster Personal (samakan dengan V1).
     */
    private const ROSTER_CODE_LABELS = [
        'd' => 'Day Shift',
        'n' => 'Night Shift',
        'off' => 'Off',
        'ct' => 'Cuti',
        'h' => 'Dirumahkan',
        'id' => 'Induksi',
        'ids' => 'Induksi Permit',
        'tr' => 'Training',
        'mcu' => 'MCU',
    ];

    /**
     * Matrix roster bulanan: satu baris per employee (yang punya jabatan_roster
     * terisi, atau punya entry di bulan ini), berisi kode roster per tanggal.
     */
    public function monthlyMatrix(int $month, int $year, ?string $search, ?string $rosterCategory): Collection
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $employees = Employee::query()
            ->search($search)
            ->when($rosterCategory, fn ($q) => $q->where('jabatan_roster', $rosterCategory))
            ->when(! $rosterCategory, fn ($q) => $q->whereNotNull('jabatan_roster')->where('jabatan_roster', '!=', ''))
            ->orderBy('name')
            ->get();

        $entries = RosterEntry::query()
            ->month($month, $year)
            ->whereIn('employee_id', $employees->pluck('id'))
            ->get()
            ->groupBy('employee_id');

        return $employees->map(function (Employee $employee) use ($entries, $daysInMonth, $month, $year) {
            $byDate = ($entries->get($employee->id) ?? collect())->keyBy(fn (RosterEntry $e) => $e->tanggal->format('Y-m-d'));

            $days = collect(range(1, $daysInMonth))->map(function (int $day) use ($byDate, $month, $year) {
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $entry = $byDate->get($date);

                return ['tanggal' => $date, 'kode' => $entry?->kode, 'unit' => $entry?->unit];
            });

            return [
                'employeeId' => $employee->id,
                'nrp' => $employee->nrp,
                'nama' => $employee->name,
                'jabatan' => $employee->jabatan_ss6,
                'jabatanRoster' => $employee->jabatan_roster,
                'unit' => $employee->unit,
                'days' => $days->values(),
            ];
        })->values();
    }

    /**
     * Roster kalender bulanan untuk satu karyawan (dipakai halaman Roster Personal).
     * Mengembalikan info karyawan + daftar hari (tanggal, kode, keterangan) untuk 1 bulan penuh.
     */
    public function personalCalendar(string $nrp, int $year, int $month): array
    {
        $employee = Employee::query()->where('nrp', $nrp)->first();

        if (! $employee) {
            return ['employee' => null, 'days' => []];
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $byDate = RosterEntry::query()
            ->month($month, $year)
            ->where('employee_id', $employee->id)
            ->get()
            ->keyBy(fn (RosterEntry $e) => $e->tanggal->format('Y-m-d'));

        $rosterUnit = $byDate->first(fn (RosterEntry $e) => filled($e->unit))?->unit;

        $days = collect(range(1, $daysInMonth))->map(function (int $day) use ($byDate, $month, $year) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $entry = $byDate->get($date);
            $kode = $entry?->kode ?? '';

            return [
                'tanggal' => $date,
                'day' => $day,
                'dayOfWeek' => (int) date('w', mktime(0, 0, 0, $month, $day, $year)),
                'kode' => $kode,
                'keterangan' => self::ROSTER_CODE_LABELS[strtolower($kode)] ?? '',
            ];
        })->values()->all();

        return [
            'employee' => [
                'employeeId' => $employee->id,
                'nrp' => $employee->nrp,
                'nama' => $employee->name,
                'jabatan' => $employee->jabatan_ss6,
                'unit' => $rosterUnit ?: $employee->unit,
            ],
            'days' => $days,
        ];
    }

    public function rosterCategoryOptions(): array
    {
        return Employee::query()
            ->whereNotNull('jabatan_roster')
            ->where('jabatan_roster', '!=', '')
            ->distinct()
            ->orderBy('jabatan_roster')
            ->pluck('jabatan_roster')
            ->all();
    }

    public function upsertOne(int $employeeId, string $tanggal, ?string $kode, ?string $unit): void
    {
        if (! $kode) {
            RosterEntry::where('employee_id', $employeeId)->where('tanggal', $tanggal)->delete();

            return;
        }

        RosterEntry::updateOrCreate(
            ['employee_id' => $employeeId, 'tanggal' => $tanggal],
            ['kode' => $kode, 'unit' => $unit]
        );
    }

    public function importFromExcel(UploadedFile $file): int
    {
        $import = new RosterImport;

        Excel::import($import, $file);

        return $import->importedCount();
    }
}
