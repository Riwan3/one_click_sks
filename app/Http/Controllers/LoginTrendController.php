<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginTrendRequest;
use App\Services\LoginTrendService;
use Inertia\Inertia;
use Inertia\Response;

class LoginTrendController extends Controller
{
    private const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index(LoginTrendRequest $request, LoginTrendService $service): Response
    {
        [$month, $year] = [$request->month(), $request->year()];
        $never = $service->neverLoggedIn($request->neverPage())->withQueryString();

        return Inertia::render('LoginTrend/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'filters' => ['bulan' => $month, 'tahun' => $year, 'years' => $request->yearOptions()],
            'monthNames' => self::MONTH_NAMES,
            'report' => $service->monthlyReport($month, $year),
            'never' => [
                'rows' => $never->items(),
                'total' => $never->total(),
                'currentPage' => $never->currentPage(),
                'lastPage' => $never->lastPage(),
                'from' => $never->firstItem() ?? 0,
                'to' => $never->lastItem() ?? 0,
            ],
        ]);
    }
}
