<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMorDataRequest;
use App\Http\Requests\UpdateMorDataRequest;
use App\Http\Resources\MorDataResource;
use App\Models\Employee;
use App\Models\MorData;
use App\Services\MorDataService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MorDataController extends Controller
{
    private const PER_PAGE = 15;

    private const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function __construct(private MorDataService $morDataService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $bulan = (int) $request->integer('bulan', now()->month);
        $bulan = $bulan >= 1 && $bulan <= 12 ? $bulan : now()->month;
        $tahun = (int) $request->integer('tahun', now()->year);
        $page = max(1, (int) $request->integer('page', 1));

        $items = $this->morDataService->paginate($search ?: null, $bulan, $tahun, $page, self::PER_PAGE);

        return Inertia::render('OnePower/MorData/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'filters' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'monthNames' => self::MONTH_NAMES,
                'years' => range(now()->year - 2, now()->year),
            ],
            'items' => MorDataResource::collection($items->items())->resolve(),
            'pagination' => [
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function store(StoreMorDataRequest $request): RedirectResponse
    {
        $this->morDataService->create($request->validated());

        return redirect()->back()->with('success', 'Data MOR berhasil ditambahkan.');
    }

    public function update(UpdateMorDataRequest $request, MorData $morDatum): RedirectResponse
    {
        $this->morDataService->update($morDatum, $request->validated());

        return redirect()->back()->with('success', 'Data MOR berhasil diperbarui.');
    }

    public function destroy(MorData $morDatum): RedirectResponse
    {
        $this->morDataService->delete($morDatum);

        return redirect()->back()->with('success', 'Data MOR berhasil dihapus.');
    }

    public function searchEmployee(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->string('keyword'));

        if ($keyword === '') {
            return response()->json([]);
        }

        $employees = Employee::query()
            ->search($keyword)
            ->limit(10)
            ->get(['id', 'nrp', 'name', 'jabatan_ss6', 'unit']);

        return response()->json($employees);
    }

    private function notifCounts(): array
    {
        return ['signature' => 2, 'notification' => 3, 'message' => 1];
    }
}
