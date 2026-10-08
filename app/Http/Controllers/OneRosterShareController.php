<?php

namespace App\Http\Controllers;

use App\Services\RosterShareService;
use App\Support\MenuAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OneRosterShareController extends Controller
{
    private const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /** Terbuka untuk semua user login (seperti V1): admin memilih karyawan, role lain hanya melihat miliknya. */
    public function index(Request $request, RosterShareService $service): Response
    {
        $month = (int) $request->query('bulan', 0);
        $year = (int) $request->query('tahun', date('Y'));
        $year = $year >= 2020 && $year <= 2100 ? $year : (int) date('Y');
        $nrp = $request->query('nrp');

        $share = $service->build(
            $request->user(),
            $year,
            $month >= 1 && $month <= 12 ? $month : null,
            is_string($nrp) ? trim($nrp) : '',
        );

        return Inertia::render('OneRoster/Share/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_roster_menu'),
            'share' => $share,
            'monthNames' => self::MONTH_NAMES,
        ]);
    }
}
