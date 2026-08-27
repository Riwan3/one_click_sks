<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreValidasiIstirahatRequest;
use App\Http\Requests\UpdateValidasiIstirahatRequest;
use App\Http\Resources\ValidasiIstirahatResource;
use App\Models\Employee;
use App\Models\ValidasiIstirahat;
use App\Services\ValidasiIstirahatService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ValidasiIstirahatController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private ValidasiIstirahatService $validasiIstirahatService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));
        $page = max(1, (int) $request->integer('page', 1));

        $items = $this->validasiIstirahatService->paginate($search, $dateFrom ?: null, $dateTo ?: null, $page, self::PER_PAGE);

        return Inertia::render('OnePower/ValidasiIstirahat/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'items' => ValidasiIstirahatResource::collection($items->items())->resolve(),
            'pagination' => [
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('OnePower/ValidasiIstirahat/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
        ]);
    }

    public function store(StoreValidasiIstirahatRequest $request): RedirectResponse
    {
        $this->validasiIstirahatService->create($request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/validasi-istirahat')->with('success', 'Validasi istirahat berhasil dibuat.');
    }

    public function edit(ValidasiIstirahat $validasiIstirahat): Response
    {
        return Inertia::render('OnePower/ValidasiIstirahat/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetItem' => new ValidasiIstirahatResource($validasiIstirahat),
        ]);
    }

    public function update(UpdateValidasiIstirahatRequest $request, ValidasiIstirahat $validasiIstirahat): RedirectResponse
    {
        $this->validasiIstirahatService->update($validasiIstirahat, $request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/validasi-istirahat')->with('success', 'Validasi istirahat berhasil diperbarui.');
    }

    public function destroy(ValidasiIstirahat $validasiIstirahat): RedirectResponse
    {
        $this->validasiIstirahatService->delete($validasiIstirahat);

        return redirect()->back()->with('success', 'Validasi istirahat berhasil dihapus.');
    }

    public function show(ValidasiIstirahat $validasiIstirahat): Response
    {
        return Inertia::render('OnePower/ValidasiIstirahat/Print', [
            'item' => new ValidasiIstirahatResource($validasiIstirahat),
        ]);
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
