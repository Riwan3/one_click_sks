<?php

namespace App\Http\Controllers;

use App\Http\Resources\MorDataResource;
use App\Models\MorData;
use App\Support\MenuAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ManpowerPerformanceReportController extends Controller
{
    private const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index(Request $request): Response
    {
        $bulan = (int) $request->integer('bulan', now()->month);
        $bulan = $bulan >= 1 && $bulan <= 12 ? $bulan : now()->month;
        $tahun = (int) $request->integer('tahun', now()->year);

        $items = MorData::query()
            ->with('employee')
            ->period($bulan, $tahun)
            ->orderByDesc('total_poin')
            ->get();

        return Inertia::render('OnePower/ManpowerPerformanceReport/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => ['signature' => 2, 'notification' => 3, 'message' => 1],
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'filters' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'monthNames' => self::MONTH_NAMES,
                'years' => range(now()->year - 2, now()->year),
            ],
            'items' => MorDataResource::collection($items)->resolve(),
        ]);
    }
}
