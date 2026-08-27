<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Support\MenuAccess;
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

    public function __construct(private DashboardService $dashboardService) {}

    public function dashboard(Request $request): Response
    {
        $month = (int) $request->integer('bulan', now()->month);
        $month = $month >= 1 && $month <= 12 ? $month : now()->month;
        $year = (int) $request->integer('tahun', now()->year);

        $hm = $this->dashboardService->hmStats($month, $year);
        $attendance = $this->dashboardService->attendanceStats($month, $year);
        $deviasi = $this->dashboardService->deviasiStats();

        return Inertia::render('OnePower/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => [
                'signature' => 2,
                'notification' => 3,
                'message' => 1,
            ],
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'filters' => [
                'bulan' => $month,
                'tahun' => $year,
                'monthNames' => self::MONTH_NAMES,
                'years' => range(now()->year - 2, now()->year),
            ],
            'stats' => $hm,
            'dailyTrend' => $this->dashboardService->hmDailyTrend($month, $year),
            'yearlyTrend' => $this->dashboardService->hmYearlyTrend($year),
            'topOperators' => $this->dashboardService->hmTopOperators($month, $year),
            'attendance' => $attendance,
            'deviasi' => $deviasi,
        ]);
    }
}
