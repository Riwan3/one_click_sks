<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFatigueInterviewRequest;
use App\Http\Requests\UpdateFatigueInterviewRequest;
use App\Http\Resources\FatigueInterviewResource;
use App\Models\Employee;
use App\Models\FatigueInterview;
use App\Services\FatigueInterviewService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FatigueInterviewController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private FatigueInterviewService $fatigueInterviewService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));
        $page = max(1, (int) $request->integer('page', 1));

        $items = $this->fatigueInterviewService->paginate($search, $dateFrom ?: null, $dateTo ?: null, $page, self::PER_PAGE);

        return Inertia::render('OnePower/FatigueInterview/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'items' => FatigueInterviewResource::collection($items->items())->resolve(),
            'pagination' => [
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('OnePower/FatigueInterview/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
        ]);
    }

    public function store(StoreFatigueInterviewRequest $request): RedirectResponse
    {
        $this->fatigueInterviewService->create($request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/interview-fatigue')->with('success', 'Interview fatigue berhasil dibuat.');
    }

    public function edit(FatigueInterview $fatigueInterview): Response
    {
        $fatigueInterview->load(['employee', 'pengawas']);

        return Inertia::render('OnePower/FatigueInterview/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetItem' => new FatigueInterviewResource($fatigueInterview),
        ]);
    }

    public function update(UpdateFatigueInterviewRequest $request, FatigueInterview $fatigueInterview): RedirectResponse
    {
        $this->fatigueInterviewService->update($fatigueInterview, $request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/interview-fatigue')->with('success', 'Interview fatigue berhasil diperbarui.');
    }

    public function destroy(FatigueInterview $fatigueInterview): RedirectResponse
    {
        $this->fatigueInterviewService->delete($fatigueInterview);

        return redirect()->back()->with('success', 'Interview fatigue berhasil dihapus.');
    }

    public function show(FatigueInterview $fatigueInterview): Response
    {
        $fatigueInterview->load(['employee', 'pengawas']);

        return Inertia::render('OnePower/FatigueInterview/Print', [
            'item' => new FatigueInterviewResource($fatigueInterview),
        ]);
    }

    public function searchEmployee(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->string('keyword'));

        if ($keyword === '') {
            return response()->json([]);
        }

        $user = $request->user();

        // Row-level scoping: GL non-Manpower search dibatasi ke employee di unit mereka
        $employees = Employee::query()
            ->search($keyword)
            ->when(
                $user && !$user->isAdmin() && !$user->isPimpinan() && !$user->isGlManpower(),
                fn ($q) => $q->where('unit', $user->employee?->unit)
            )
            ->limit(10)
            ->get(['id', 'nrp', 'name', 'jabatan_ss6', 'unit']);

        return response()->json($employees);
    }

    private function notifCounts(): array
    {
        return ['signature' => 2, 'notification' => 3, 'message' => 1];
    }
}
