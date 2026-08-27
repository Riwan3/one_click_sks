<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePemulanganKaryawanRequest;
use App\Http\Requests\UpdatePemulanganKaryawanRequest;
use App\Http\Resources\PemulanganKaryawanResource;
use App\Models\Employee;
use App\Models\PemulanganKaryawan;
use App\Services\PemulanganKaryawanService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PemulanganKaryawanController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private PemulanganKaryawanService $pemulanganKaryawanService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));
        $page = max(1, (int) $request->integer('page', 1));

        $items = $this->pemulanganKaryawanService->paginate($search, $dateFrom ?: null, $dateTo ?: null, $page, self::PER_PAGE);

        return Inertia::render('OnePower/PemulanganKaryawan/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'items' => PemulanganKaryawanResource::collection($items->items())->resolve(),
            'pagination' => [
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('OnePower/PemulanganKaryawan/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
        ]);
    }

    public function store(StorePemulanganKaryawanRequest $request): RedirectResponse
    {
        $this->pemulanganKaryawanService->create($request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/pemulangan-karyawan')->with('success', 'Data pemulangan karyawan berhasil dibuat.');
    }

    public function edit(PemulanganKaryawan $pemulanganKaryawan): Response
    {
        return Inertia::render('OnePower/PemulanganKaryawan/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetItem' => new PemulanganKaryawanResource($pemulanganKaryawan),
        ]);
    }

    public function update(UpdatePemulanganKaryawanRequest $request, PemulanganKaryawan $pemulanganKaryawan): RedirectResponse
    {
        $this->pemulanganKaryawanService->update($pemulanganKaryawan, $request->validated());

        return redirect('/one-power/pembinaan/pemulangan-karyawan')->with('success', 'Data pemulangan karyawan berhasil diperbarui.');
    }

    public function destroy(PemulanganKaryawan $pemulanganKaryawan): RedirectResponse
    {
        $this->pemulanganKaryawanService->delete($pemulanganKaryawan);

        return redirect()->back()->with('success', 'Data pemulangan karyawan berhasil dihapus.');
    }

    public function show(PemulanganKaryawan $pemulanganKaryawan): Response
    {
        return Inertia::render('OnePower/PemulanganKaryawan/Print', [
            'item' => new PemulanganKaryawanResource($pemulanganKaryawan),
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
