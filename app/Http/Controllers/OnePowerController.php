<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnePowerController extends Controller
{
    private const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    private const OPERATOR_NAMES = [
        'Ahmad Fauzi', 'Budi Santoso', 'Candra Wijaya', 'Dedi Kurniawan', 'Eko Prasetyo',
        'Fajar Nugroho', 'Gilang Ramadhan', 'Hendra Saputra', 'Irfan Hakim', 'Joko Susilo',
    ];

    /**
     * Dummy data — ganti dengan query database begitu backend One-Power siap.
     */
    public function dashboard(Request $request): Response
    {
        $month = (int) $request->integer('bulan', now()->month);
        $month = $month >= 1 && $month <= 12 ? $month : now()->month;
        $year = (int) $request->integer('tahun', now()->year);

        $seed = $month * 31 + $year;

        return Inertia::render('OnePower/Dashboard', [
            'user' => [
                'displayName' => session('dummy_user', 'Guest User'),
                'username' => session('dummy_username', '00000'),
                'jabatan' => 'Data Analyst',
                'unit' => 'MASTER',
                'foto' => null,
            ],
            'notifCounts' => [
                'signature' => 2,
                'notification' => 3,
                'message' => 1,
            ],
            'menu' => config('one_power_menu'),
            'filters' => [
                'bulan' => $month,
                'tahun' => $year,
                'monthNames' => self::MONTH_NAMES,
                'years' => range(now()->year - 2, now()->year),
            ],
            'stats' => $this->dummyStats($seed),
            'dailyTrend' => $this->dummyDailyTrend($month, $year, $seed),
            'yearlyTrend' => $this->dummyYearlyTrend($year),
            'topOperators' => $this->dummyTopOperators($seed),
        ]);
    }

    private function dummyStats(int $seed): array
    {
        $avgHm = round(6 + (sin($seed) + 1) * 2, 1);

        return [
            'totalHm' => round($avgHm * (80 + $seed % 20), 1),
            'avgHm' => $avgHm,
            'activeOperators' => 70 + $seed % 15,
            'totalRecords' => 900 + $seed % 300,
        ];
    }

    private function dummyDailyTrend(int $month, int $year, int $seed): array
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        return collect(range(1, $daysInMonth))->map(fn (int $day) => [
            'label' => str_pad((string) $day, 2, '0', STR_PAD_LEFT),
            'value' => round(6 + sin(($day + $seed) / 3) * 2 + ($day % 5) * 0.3, 1),
        ])->all();
    }

    private function dummyYearlyTrend(int $year): array
    {
        return collect(range(1, 12))->map(fn (int $month) => [
            'label' => substr(self::MONTH_NAMES[$month], 0, 3),
            'value' => round(180 + sin(($month + $year) / 2) * 40 + $month * 3, 1),
        ])->all();
    }

    private function dummyTopOperators(int $seed): array
    {
        return collect(self::OPERATOR_NAMES)
            ->map(fn (string $name, int $index) => [
                'label' => $name,
                'value' => round(180 - $index * 9 + (($seed + $index) % 7), 1),
            ])
            ->sortByDesc('value')
            ->values()
            ->all();
    }
}
