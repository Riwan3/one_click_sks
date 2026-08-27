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
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from')) ?: now()->startOfMonth()->format('Y-m-d');
        $dateTo = trim((string) $request->string('date_to')) ?: now()->format('Y-m-d');
        $page = max(1, (int) $request->integer('page', 1));

        $hmData = $this->hmDataService->paginate($search, $dateFrom, $dateTo, null, $page, self::PER_PAGE);
        $summary = $this->hmDataService->summary($search, $dateFrom, $dateTo, null);

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
