<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LoginLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Trend login semua user per bulan: per minggu x shift, user unik, dan karyawan yang belum pernah login (padanan V1 LoginTrendController). */
class LoginTrendService
{
    public const NEVER_PER_PAGE = 10;

    private const SHIFT1 = ['07:00:00', '18:30:00'];

    private const SHIFT2 = ['19:00:00', '06:30:00'];

    private const WEEK_DAYS = 7;

    /** @return array{series: array<string, array<string, int>>, totals: array<string, int>, uniqueUserCount: int, uniqueUsers: array<int, array<string, mixed>>} */
    public function monthlyReport(int $month, int $year): array
    {
        $start = CarbonImmutable::create($year, $month, 1)->startOfDay();
        $logs = $this->successfulLogins()
            ->whereBetween('login_at', [$start, $start->endOfMonth()])
            ->orderBy('login_at')
            ->get(['user_id', 'username', 'nama', 'ip_address', 'login_at']);

        $series = $this->emptySeries($start->daysInMonth);
        $totals = ['total' => 0, 'shift1' => 0, 'shift2' => 0, 'lainnya' => 0];

        foreach ($logs as $log) {
            $shift = $this->detectShift($log->login_at->format('H:i:s'));
            $series[$this->weekLabel($log->login_at->day, $start->daysInMonth)][$shift]++;
            $totals['total']++;
            $totals[$shift]++;
        }

        return [
            'series' => $series,
            'totals' => $totals,
            'uniqueUserCount' => $logs->pluck('username')->unique()->count(),
            'uniqueUsers' => $this->uniqueUsers($logs),
        ];
    }

    /** Karyawan aktif yang belum pernah punya login sukses, terpaginasi. */
    public function neverLoggedIn(int $page): LengthAwarePaginator
    {
        return Employee::query()
            ->where(fn ($query) => $query->whereNull('status_karyawan')->orWhere('status_karyawan', 'not like', '%INACTIVE%'))
            ->whereNotIn('nrp', $this->successfulLogins()->select('username'))
            ->orderBy('name')
            ->paginate(self::NEVER_PER_PAGE, ['nrp', 'name', 'jabatan_ss6'], 'page_never', $page);
    }

    public function detectShift(string $time): string
    {
        return match (true) {
            $time >= self::SHIFT1[0] && $time <= self::SHIFT1[1] => 'shift1',
            $time >= self::SHIFT2[0] || $time <= self::SHIFT2[1] => 'shift2',
            default => 'lainnya',
        };
    }

    private function successfulLogins(): Builder
    {
        return LoginLog::query()->where('status', 'SUCCESS');
    }

    private function weekLabel(int $day, int $daysInMonth): string
    {
        $index = intdiv($day - 1, self::WEEK_DAYS);
        $first = $index * self::WEEK_DAYS + 1;

        return sprintf('Minggu %d (%d-%d)', $index + 1, $first, min($first + self::WEEK_DAYS - 1, $daysInMonth));
    }

    private function emptySeries(int $daysInMonth): array
    {
        return collect(range(1, $daysInMonth, self::WEEK_DAYS))
            ->mapWithKeys(fn (int $day) => [$this->weekLabel($day, $daysInMonth) => ['shift1' => 0, 'shift2' => 0, 'lainnya' => 0]])
            ->all();
    }

    /** Satu baris per NRP: jumlah login, IP & nama dari login terakhir, urut jumlah login terbanyak. */
    private function uniqueUsers(Collection $logs): array
    {
        $grouped = $logs->groupBy('username');
        $jabatan = $this->jabatanByNrp($grouped->keys()->map(fn ($nrp) => (string) $nrp)->all(), $logs);

        return $grouped
            ->map(fn (Collection $rows, $nrp) => [
                'nrp' => (string) $nrp,
                'nama' => $rows->last()->nama,
                'jabatan' => $jabatan[(string) $nrp] ?? null,
                'login_count' => $rows->count(),
                'ip_address' => $rows->last()->ip_address,
                'last_login' => $rows->last()->login_at->format('Y-m-d H:i:s'),
            ])
            ->sortByDesc('login_count')
            ->values()
            ->all();
    }

    /** Jabatan SS6 karyawan; cadangan: jabatan akun user. */
    private function jabatanByNrp(array $nrps, Collection $logs): array
    {
        $fromEmployee = Employee::query()->whereIn('nrp', $nrps)->pluck('jabatan_ss6', 'nrp');
        $fromUser = User::query()->whereIn('id', $logs->pluck('user_id')->filter()->unique())->pluck('jabatan', 'id');

        return collect($nrps)->mapWithKeys(fn (string $nrp) => [
            $nrp => $fromEmployee[$nrp] ?? $fromUser[$logs->firstWhere('username', $nrp)?->user_id] ?? null,
        ])->all();
    }
}
