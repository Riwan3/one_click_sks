<?php

namespace App\Http\Controllers;

use App\Services\RosterService;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RosterController extends Controller
{
    private const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function __construct(private RosterService $rosterService) {}

    public function index(Request $request): Response
    {
        $month = (int) $request->integer('bulan', now()->month);
        $month = $month >= 1 && $month <= 12 ? $month : now()->month;
        $year = (int) $request->integer('tahun', now()->year);
        $search = trim((string) $request->string('search'));
        $category = trim((string) $request->string('kategori')) ?: null;

        return Inertia::render('OnePower/Roster/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'filters' => [
                'bulan' => $month,
                'tahun' => $year,
                'search' => $search,
                'kategori' => $category,
                'monthNames' => self::MONTH_NAMES,
                'years' => range(now()->year - 2, now()->year + 1),
                'kategoriOptions' => $this->rosterService->rosterCategoryOptions(),
            ],
            'rows' => $this->rosterService->monthlyMatrix($month, $year, $search ?: null, $category),
        ]);
    }

    public function updateCell(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'tanggal' => ['required', 'date'],
            'kode' => ['nullable', 'string', 'max:20'],
            'unit' => ['nullable', 'string', 'max:50'],
        ]);

        $this->rosterService->upsertOne($data['employee_id'], $data['tanggal'], $data['kode'] ?? null, $data['unit'] ?? null);

        return redirect()->back()->with('success', 'Roster berhasil diperbarui.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $count = $this->rosterService->importFromExcel($request->file('file'));

        return redirect()->back()->with('success', "Berhasil mengimport {$count} data roster.");
    }
}
