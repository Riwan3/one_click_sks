<?php

namespace App\Services;

use App\Models\SettingBoardView;
use App\Models\User;
use Carbon\CarbonImmutable;

/** Catat & rangkum pembukaan papan Setting Manpower / Setting Fleet oleh pengguna non-admin. */
class SettingBoardViewService
{
    public const TYPES = ['manpower', 'fleet'];

    /** Refresh berulang dalam jendela ini tidak dihitung ulang. */
    private const DEDUPE_MINUTES = 10;

    /**
     * Catat satu pembukaan per papan yang tampil (punya data). Admin tidak dicatat.
     *
     * @param  array<string, bool>  $shown  ['manpower' => bool, 'fleet' => bool]
     */
    public function record(User $user, string $date, string $shift, array $shown): void
    {
        if ($user->isAdmin()) {
            return;
        }

        foreach (self::TYPES as $type) {
            if (empty($shown[$type]) || $this->wasRecentlyRecorded($user, $type, $date, $shift)) {
                continue;
            }

            SettingBoardView::create([
                'user_id' => $user->id, 'board_type' => $type, 'work_date' => $date, 'shift' => $shift, 'viewed_at' => now(),
            ]);
        }
    }

    /**
     * Rangkuman satu bulan: tiap hari (semua tanggal bulan itu) + total per papan + jumlah pengguna unik.
     *
     * @return array{days: array<int, array{date: string, manpower: int, fleet: int}>, totals: array{manpower: int, fleet: int, users: int}}
     */
    public function monthlyStats(string $month): array
    {
        $start = CarbonImmutable::createFromFormat('Y-m-d', $month.'-01')->startOfDay();
        $end = $start->endOfMonth();

        $rows = SettingBoardView::query()
            ->whereBetween('viewed_at', [$start, $end])
            ->get(['user_id', 'board_type', 'viewed_at']);

        $perDay = $rows->groupBy(fn ($row) => $row->viewed_at->format('Y-m-d'));

        $days = collect(range(1, $start->daysInMonth))->map(function (int $day) use ($start, $perDay) {
            $date = $start->day($day)->format('Y-m-d');
            $byType = ($perDay[$date] ?? collect())->countBy('board_type');

            return ['date' => $date, 'manpower' => $byType['manpower'] ?? 0, 'fleet' => $byType['fleet'] ?? 0];
        })->all();

        $totals = $rows->countBy('board_type');

        return [
            'days' => $days,
            'totals' => ['manpower' => $totals['manpower'] ?? 0, 'fleet' => $totals['fleet'] ?? 0, 'users' => $rows->pluck('user_id')->unique()->count()],
        ];
    }

    private function wasRecentlyRecorded(User $user, string $type, string $date, string $shift): bool
    {
        return SettingBoardView::query()
            ->where(['user_id' => $user->id, 'board_type' => $type, 'work_date' => $date, 'shift' => $shift])
            ->where('viewed_at', '>=', now()->subMinutes(self::DEDUPE_MINUTES))
            ->exists();
    }
}
