<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHmDataRequest;
use App\Http\Requests\UpdateHmDataRequest;
use App\Http\Resources\HmDataResource;
use App\Models\Employee;
use App\Models\HmData;
use App\Services\HmDataService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HmDataController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private HmDataService $hmDataService) {}

    public function index(Request $request): Response
    {
        $user = $request->user()?->loadMissing('employee');
        $isOperator = (bool) ($user && (
            strtolower((string) $user->role) === 'operator'
            || str_contains(strtoupper((string) ($user->employee?->jabatan_ss6 ?? $user->jabatan ?? '')), 'OPERATOR')
            || str_contains(strtoupper((string) ($user->role ?? '')), 'OPERATOR')
        ));

        $employeeId = $isOperator ? $user->employee?->id : null;

        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from')) ?: now()->startOfMonth()->format('Y-m-d');
        $dateTo = trim((string) $request->string('date_to')) ?: now()->format('Y-m-d');
        $page = max(1, (int) $request->integer('page', 1));

        $hmData = $this->hmDataService->paginate($search, $dateFrom, $dateTo, $employeeId, $page, self::PER_PAGE);
        $summary = $this->hmDataService->summary($search, $dateFrom, $dateTo, $employeeId);

        return Inertia::render('OnePower/HmData/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'summary' => $summary,
            'hmData' => HmDataResource::collection($hmData->items())->resolve(),
            'pagination' => [
                'currentPage' => $hmData->currentPage(),
                'lastPage' => $hmData->lastPage(),
                'total' => $hmData->total(),
            ],
        ]);
    }

    /**
     * API mobile "HM Saya": selalu HM milik karyawan yang login, per bulan (?bulan=&tahun=&page=).
     * Akun tanpa data karyawan mendapat daftar kosong (bukan HM semua karyawan).
     */
    public function api(Request $request): JsonResponse
    {
        $employee = $request->user()?->loadMissing('employee')->employee;

        $month = (int) $request->integer('bulan', now()->month);
        $month = $month >= 1 && $month <= 12 ? $month : now()->month;
        $year = (int) $request->integer('tahun', now()->year);
        $start = now()->setDate($year, $month, 1)->startOfDay();
        $dateFrom = $start->format('Y-m-d');
        $dateTo = $start->copy()->endOfMonth()->format('Y-m-d');
        $page = max(1, (int) $request->integer('page', 1));

        if (! $employee) {
            return response()->json([
                'bulan' => $month,
                'tahun' => $year,
                'summary' => ['totalHm' => 0, 'avgHm' => 0, 'totalRecords' => 0],
                'items' => [],
                'pagination' => ['currentPage' => 1, 'lastPage' => 1, 'total' => 0],
            ]);
        }

        $hmData = $this->hmDataService->paginate(null, $dateFrom, $dateTo, $employee->id, $page, 20);

        return response()->json([
            'bulan' => $month,
            'tahun' => $year,
            'summary' => $this->hmDataService->summary(null, $dateFrom, $dateTo, $employee->id),
            'items' => HmDataResource::collection($hmData->items())->resolve(),
            'pagination' => [
                'currentPage' => $hmData->currentPage(),
                'lastPage' => $hmData->lastPage(),
                'total' => $hmData->total(),
            ],
        ]);
    }

    public function store(StoreHmDataRequest $request): RedirectResponse
    {
        $this->hmDataService->create($request->validated(), $request->user()?->id);

        return redirect('/one-power/personal-data/hm')->with('success', 'Data HM berhasil ditambahkan.');
    }

    public function update(UpdateHmDataRequest $request, HmData $hm): RedirectResponse
    {
        $this->hmDataService->update($hm, $request->validated());

        return redirect('/one-power/personal-data/hm')->with('success', 'Data HM berhasil diperbarui.');
    }

    public function destroy(HmData $hm): RedirectResponse
    {
        $this->hmDataService->delete($hm);

        return redirect()->back()->with('success', 'Data HM berhasil dihapus.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $count = $this->hmDataService->importFromExcel($request->file('file'), $request->user()?->id);

        return redirect('/one-power/personal-data/hm')->with('success', "Berhasil mengimport {$count} data HM.");
    }

    public function importFromApi(Request $request): RedirectResponse
    {
        $request->validate([
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'min:2000'],
        ]);

        $result = $this->hmDataService->importFromApi(
            $request->integer('bulan'),
            $request->integer('tahun'),
            $request->user()?->id
        );

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect('/one-power/personal-data/hm')->with('success', $result['message']);
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
}
