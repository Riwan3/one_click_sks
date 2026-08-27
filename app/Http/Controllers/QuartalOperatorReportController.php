<?php

namespace App\Http\Controllers;

use App\Models\MorData;
use App\Support\MenuAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuartalOperatorReportController extends Controller
{
    private const QUARTERS = [
        1 => [1, 2, 3], 2 => [4, 5, 6], 3 => [7, 8, 9], 4 => [10, 11, 12],
    ];

    public function index(Request $request): Response
    {
        $quartal = (int) $request->integer('quartal', ceil(now()->month / 3));
        $quartal = $quartal >= 1 && $quartal <= 4 ? $quartal : 1;
        $tahun = (int) $request->integer('tahun', now()->year);
        $months = self::QUARTERS[$quartal];

        $items = MorData::query()
            ->with('employee')
            ->whereIn('bulan', $months)
            ->where('tahun', $tahun)
            ->selectRaw('employee_id, AVG(atr_persen) as avg_atr, AVG(timesheet_persen) as avg_timesheet, AVG(hm_ratio) as avg_hm_ratio, AVG(total_poin) as avg_total_poin')
            ->groupBy('employee_id')
            ->orderByDesc('avg_total_poin')
            ->get()
            ->map(fn (MorData $row) => [
                'employeeId' => $row->employee_id,
                'nrp' => $row->employee?->nrp,
                'nama' => $row->employee?->name,
                'avgAtr' => round((float) $row->avg_atr, 1),
                'avgTimesheet' => round((float) $row->avg_timesheet, 1),
                'avgHmRatio' => round((float) $row->avg_hm_ratio, 1),
                'avgTotalPoin' => round((float) $row->avg_total_poin, 1),
            ]);

        return Inertia::render('OnePower/QuartalOperatorReport/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => ['signature' => 2, 'notification' => 3, 'message' => 1],
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'filters' => [
                'quartal' => $quartal,
                'tahun' => $tahun,
                'years' => range(now()->year - 2, now()->year),
            ],
            'items' => $items,
        ]);
    }
}
