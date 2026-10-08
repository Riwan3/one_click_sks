<?php

namespace App\Http\Controllers;

use App\Services\RosterProduksiService;
use App\Support\MenuAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OneRosterProduksiController extends Controller
{
    private const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index(Request $request, RosterProduksiService $service): Response
    {
        $month = (int) $request->query('bulan', date('n'));
        $month = $month >= 1 && $month <= 12 ? $month : (int) date('n');
        $year = (int) $request->query('tahun', date('Y'));
        $year = $year >= 2020 && $year <= 2100 ? $year : (int) date('Y');
        $jabatan = $this->text($request, 'jabatan');
        $monitoring = $service->build($year, $month, $this->text($request, 'category'), $jabatan);

        return Inertia::render('OneRoster/Produksi/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_roster_menu'),
            'monitoring' => $monitoring,
            'filters' => [
                'category' => $monitoring['category'],
                'jabatan' => $jabatan,
                'bulan' => $month,
                'tahun' => $year,
            ],
            'monthNames' => self::MONTH_NAMES,
            'years' => range(2024, max(2026, (int) date('Y') + 1, $year)),
        ]);
    }

    private function text(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? trim($value) : '';
    }
}
