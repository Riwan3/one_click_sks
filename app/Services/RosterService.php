<?php

namespace App\Services;

use App\Imports\RosterImport;
use App\Models\Employee;
use App\Models\RosterEntry;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RosterService
{
    /**
     * Label keterangan per kode roster, dipakai kalender Roster Personal (samakan dengan V1 & Roster Departemen).
     */
    private const ROSTER_CODE_LABELS = [
        'd'   => 'Day Shift',
        'n'   => 'Night Shift',
        'off' => 'Off',
        'ct'  => 'Cuti',
        'id'  => 'Induksi',
        'ids' => 'Induksi Permit',
        'mcu' => 'MCU',
        'fu'  => 'Follow Up',
        'h'   => 'Dirumahkan',
        'tr'  => 'Training',
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
            $employeeEntries = $entries->get($employee->id, collect())
                ->keyBy(fn (RosterEntry $e) => $e->tanggal->format('Y-m-d'));

            $days = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $days[$day] = $employeeEntries->get($date)?->kode ?? '';
            }

            return [
                'employeeId' => $employee->id,
                'nrp' => $employee->nrp,
                'nama' => $employee->name,
                'jabatan' => $employee->jabatan_ss6,
                'jabatanRoster' => $employee->jabatan_roster,
                'unit' => $employee->unit,
                'days' => $days,
            ];
        });
    }

    /**
     * Roster kalender bulanan untuk satu karyawan (dipakai halaman Roster Personal).
     * Mengambil data langsung dari roster_monthly_master (tabel utama yang dipakai roster-departemen).
     */
    public function personalCalendar(string $nrp, int $year, int $month): array
    {
        $employee = Employee::query()->where('nrp', $nrp)->first();

        if (! $employee) {
            $employee = Employee::query()->whereRaw('TRIM(nrp) = ?', [trim($nrp)])->first();
        }

        if (! $employee) {
            return ['employee' => null, 'days' => []];
        }

        $daysInMonth = (int) cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // Ambil data roster dari tabel utama yang dipakai roster-departemen (roster_monthly_master)
        $masterEntries = DB::table('roster_monthly_master')
            ->where('nrp', (string) $employee->nrp)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->get();

        $byDate = $masterEntries->keyBy(function ($e) {
            return date('Y-m-d', strtotime($e->tanggal));
        });

        // Fallback ke tabel roster_entries jika data di roster_monthly_master belum ada
        if ($byDate->isEmpty()) {
            $byDate = RosterEntry::query()
                ->month($month, $year)
                ->where('employee_id', $employee->id)
                ->get()
                ->keyBy(fn (RosterEntry $e) => $e->tanggal->format('Y-m-d'));
        }

        $rosterUnit = $masterEntries->first(fn ($e) => filled($e->unit))?->unit
            ?: ($byDate->first(fn ($e) => filled($e->unit))?->unit ?? null);

        $days = collect(range(1, $daysInMonth))->map(function (int $day) use ($byDate, $month, $year) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $entry = $byDate->get($date);
            $kode = strtoupper(trim((string) ($entry?->kode ?? '')));

            return [
                'tanggal' => $date,
                'day' => $day,
                'dayOfWeek' => (int) date('w', mktime(0, 0, 0, $month, $day, $year)),
                'kode' => $kode,
                'keterangan' => self::ROSTER_CODE_LABELS[strtolower($kode)] ?? ($kode ? $kode : ''),
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
