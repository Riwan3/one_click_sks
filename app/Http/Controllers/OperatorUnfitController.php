<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOperatorUnfitRequest;
use App\Http\Requests\UpdateOperatorUnfitRequest;
use App\Http\Resources\OperatorUnfitResource;
use App\Models\Employee;
use App\Models\OperatorUnfit;
use App\Services\OperatorUnfitService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OperatorUnfitController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private OperatorUnfitService $operatorUnfitService) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->canAccessUnfitData(), 403);

        $search = trim((string) $request->string('search'));
        $page = max(1, (int) $request->integer('page', 1));

        $unfits = $this->operatorUnfitService->paginate($search, $page, self::PER_PAGE);

        return Inertia::render('OnePower/OperatorUnfit/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'unfits' => OperatorUnfitResource::collection($unfits->items())->resolve(),
            'pagination' => [
                'currentPage' => $unfits->currentPage(),
                'lastPage' => $unfits->lastPage(),
                'total' => $unfits->total(),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->canAccessUnfitData(), 403);

        return Inertia::render('OnePower/OperatorUnfit/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
        ]);
    }

    public function store(StoreOperatorUnfitRequest $request): RedirectResponse
    {
        abort_unless($request->user()?->canAccessUnfitData(), 403);

        $this->operatorUnfitService->create($request->safe()->except('bukti'), $request->file('bukti'), $request->user()?->id);

        return redirect('/one-power/deviasi-operator/unfit')->with('success', 'Data operator unfit berhasil ditambahkan.');
    }

    public function edit(Request $request, OperatorUnfit $unfit): Response
    {
        abort_unless($request->user()?->canAccessUnfitData(), 403);

        return Inertia::render('OnePower/OperatorUnfit/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetUnfit' => new OperatorUnfitResource($unfit),
        ]);
    }

    public function update(UpdateOperatorUnfitRequest $request, OperatorUnfit $unfit): RedirectResponse
    {
        abort_unless($request->user()?->canAccessUnfitData(), 403);

        $this->operatorUnfitService->update($unfit, $request->safe()->except('bukti'), $request->file('bukti'));

        return redirect('/one-power/deviasi-operator/unfit')->with('success', 'Data operator unfit berhasil diperbarui.');
    }

    public function destroy(Request $request, OperatorUnfit $unfit): RedirectResponse
    {
        abort_unless($request->user()?->canAccessUnfitData(), 403);

        $this->operatorUnfitService->delete($unfit);

        return redirect()->back()->with('success', 'Data operator unfit berhasil dihapus.');
    }

    public function endUnfit(Request $request, OperatorUnfit $unfit): RedirectResponse
    {
        abort_unless($request->user()?->canAccessUnfitData(), 403);

        $request->validate(['tgl_akhir_unfit' => ['nullable', 'date']]);

        $this->operatorUnfitService->endUnfit($unfit, $request->string('tgl_akhir_unfit')->toString() ?: null);

        return redirect()->back()->with('success', 'Status unfit operator berhasil diakhiri.');
    }

    public function searchEmployee(Request $request): JsonResponse
    {
        abort_unless($request->user()?->canAccessUnfitData(), 403);

        $keyword = trim((string) $request->string('keyword'));

        if ($keyword === '') {
            return response()->json([]);
        }

        $employees = Employee::query()
            ->search($keyword)
            ->limit(10)
            ->get(['nrp', 'name', 'jabatan_ss6', 'unit', 'no_telepon']);

        return response()->json($employees);
    }

    private function notifCounts(): array
    {
        return ['signature' => 2, 'notification' => 3, 'message' => 1];
    }
}
