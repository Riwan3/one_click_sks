<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoachingCounselingRequest;
use App\Http\Requests\UpdateCoachingCounselingRequest;
use App\Http\Resources\CoachingCounselingResource;
use App\Models\CoachingCounseling;
use App\Models\Employee;
use App\Services\CoachingCounselingService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\StreamedResponse;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Inertia\Inertia;
use Inertia\Response;

class CoachingCounselingController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private CoachingCounselingService $coachingCounselingService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));
        $page = max(1, (int) $request->integer('page', 1));

        $items = $this->coachingCounselingService->paginate($search, $dateFrom ?: null, $dateTo ?: null, $page, self::PER_PAGE);

        return Inertia::render('OnePower/CoachingCounseling/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'items' => CoachingCounselingResource::collection($items->items())->resolve(),
            'pagination' => [
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
                'total' => $items->total(),
            ],
            'canManage' => ! $request->user()?->isManagement(),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        return Inertia::render('OnePower/CoachingCounseling/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
        ]);
    }

    public function store(StoreCoachingCounselingRequest $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->coachingCounselingService->create($request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/coaching-counseling')->with('success', 'Coaching & counseling berhasil dibuat.');
    }

    public function edit(Request $request, CoachingCounseling $coachingCounseling): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        $coachingCounseling->load(['employee', 'coach']);

        return Inertia::render('OnePower/CoachingCounseling/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetItem' => new CoachingCounselingResource($coachingCounseling),
        ]);
    }

    public function update(UpdateCoachingCounselingRequest $request, CoachingCounseling $coachingCounseling): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->coachingCounselingService->update($coachingCounseling, $request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/coaching-counseling')->with('success', 'Coaching & counseling berhasil diperbarui.');
    }

    public function destroy(Request $request, CoachingCounseling $coachingCounseling): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->coachingCounselingService->delete($coachingCounseling);

        return redirect()->back()->with('success', 'Coaching & counseling berhasil dihapus.');
    }

    public function show(CoachingCounseling $coachingCounseling): Response
    {
        $coachingCounseling->load(['employee', 'coach']);

        return Inertia::render('OnePower/CoachingCounseling/Print', [
            'item' => new CoachingCounselingResource($coachingCounseling),
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

    public function import(Request $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $count = $this->coachingCounselingService->importFromExcel($request->file('file'), $request->user()?->id);

        return redirect('/one-power/pembinaan/coaching-counseling')->with('success', "Berhasil mengimport {$count} coaching & counseling.");
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = ['nrp', 'nama_karyawan', 'jabatan', 'tanggal', 'lokasi', 'waktu_pertemuan', 'materi_pribadi', 'materi_keluarga', 'materi_lingkungan', 'materi_pekerjaan', 'fatigue_level', 'deskripsi', 'rekomendasi', 'coach'];

        return ResponseFacade::streamDownload(function () use ($headers) {
            $h = fopen('php://output', 'w');
            fputcsv($h, $headers);
            fputcsv($h, ['10001', 'Budi Santoso', 'Operator', '2026-08-26', 'CSA Inggris', '14:00', '1', '0', '1', '0', '2', 'Contoh deskripsi coaching...', 'Contoh rekomendasi...', '10002']);
            fclose($h);
        }, 'template_coaching.csv', ['Content-Type' => 'text/csv']);
    }

    private function notifCounts(): array
    {
        return ['signature' => 2, 'notification' => 3, 'message' => 1];
    }
}
