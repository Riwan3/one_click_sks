<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePemanggilanKaryawanRequest;
use App\Http\Requests\UpdatePemanggilanKaryawanRequest;
use App\Http\Resources\PemanggilanKaryawanResource;
use App\Models\Employee;
use App\Models\PemanggilanKaryawan;
use App\Services\PemanggilanKaryawanService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PemanggilanKaryawanController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private PemanggilanKaryawanService $pemanggilanKaryawanService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));
        $page = max(1, (int) $request->integer('page', 1));

        $items = $this->pemanggilanKaryawanService->paginate($search, $dateFrom ?: null, $dateTo ?: null, $page, self::PER_PAGE);

        return Inertia::render('OnePower/PemanggilanKaryawan/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'items' => PemanggilanKaryawanResource::collection($items->items())->resolve(),
            'pagination' => [
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('OnePower/PemanggilanKaryawan/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
        ]);
    }

    public function store(StorePemanggilanKaryawanRequest $request): RedirectResponse
    {
        $this->pemanggilanKaryawanService->create($request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/pemanggilan-karyawan')->with('success', 'Data pemanggilan karyawan berhasil dibuat.');
    }

    public function edit(PemanggilanKaryawan $pemanggilanKaryawan): Response
    {
        return Inertia::render('OnePower/PemanggilanKaryawan/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetItem' => new PemanggilanKaryawanResource($pemanggilanKaryawan),
        ]);
    }

    public function update(UpdatePemanggilanKaryawanRequest $request, PemanggilanKaryawan $pemanggilanKaryawan): RedirectResponse
    {
        $this->pemanggilanKaryawanService->update($pemanggilanKaryawan, $request->validated());

        return redirect('/one-power/pembinaan/pemanggilan-karyawan')->with('success', 'Data pemanggilan karyawan berhasil diperbarui.');
    }

    public function destroy(PemanggilanKaryawan $pemanggilanKaryawan): RedirectResponse
    {
        $this->pemanggilanKaryawanService->delete($pemanggilanKaryawan);

        return redirect()->back()->with('success', 'Data pemanggilan karyawan berhasil dihapus.');
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
