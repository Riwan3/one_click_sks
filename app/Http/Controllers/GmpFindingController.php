<?php

namespace App\Http\Controllers;

use App\Enums\GmpFindingStatus;
use App\Http\Requests\AssignGmpFindingPicRequest;
use App\Http\Requests\StoreGmpFindingRequest;
use App\Http\Requests\UpdateGmpFindingProgressRequest;
use App\Http\Requests\VerifyGmpFindingRequest;
use App\Http\Resources\GmpFindingResource;
use App\Models\GmpFinding;
use App\Services\GmpDashboardService;
use App\Services\GmpFindingService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Temuan & Tindak Lanjut GMP. */
class GmpFindingController extends Controller
{
    public function __construct(private GmpFindingService $findingService) {}

    public function index(Request $request): Response
    {
        $filters = $this->findingService->normalizeFilters($request->query());

        return Inertia::render('OneGmp/Temuan', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_gmp_menu'),
            'filters' => $filters,
            'stats' => $this->findingService->stats(),
            'findings' => GmpFindingResource::collection($this->findingService->list($filters))->resolve(),
            'yearOptions' => $this->findingService->yearOptions(),
            'areaOptions' => array_keys(GmpDashboardService::TYPE_LABELS),
            'priorityOptions' => GmpFinding::PRIORITIES,
            'statusOptions' => GmpFindingStatus::labels(),
            'slaDays' => $this->findingService->slaDays(),
            'employees' => $this->findingService->employeeOptions(),
        ]);
    }

    /** Detail untuk drawer (dimuat saat baris dibuka, seperti get_detail v1). */
    public function show(GmpFinding $finding): JsonResponse
    {
        $finding->load(['pic:nrp,name', 'evidences', 'histories.actor:nrp,name']);

        return response()->json((new GmpFindingResource($finding))->resolve());
    }

    public function store(StoreGmpFindingRequest $request): RedirectResponse
    {
        $this->findingService->createManual($request->validated(), $request->file('foto_sebelum'), $this->actor($request));

        return back()->with('success', 'Temuan manual berhasil dibuat.');
    }

    public function assignPic(AssignGmpFindingPicRequest $request, GmpFinding $finding): RedirectResponse
    {
        $this->findingService->assignPic($finding, $request->validated(), $this->actor($request));

        return back()->with('success', 'Informasi PIC berhasil disimpan.');
    }

    public function updateProgress(UpdateGmpFindingProgressRequest $request, GmpFinding $finding): RedirectResponse
    {
        $isSubmit = $request->validated('action_type') === 'submit_verifikasi';
        $this->findingService->updateProgress(
            $finding, $request->validated(), $request->file('foto_progress'), $request->file('foto_sesudah'), $isSubmit, $this->actor($request),
        );

        return back()->with('success', $isSubmit ? 'Laporan perbaikan dikirim untuk verifikasi.' : 'Progress berhasil disimpan.');
    }

    public function verify(VerifyGmpFindingRequest $request, GmpFinding $finding): RedirectResponse
    {
        abort_unless($finding->status === GmpFindingStatus::WaitingVerification, 422, 'Temuan belum menunggu verifikasi.');

        $this->findingService->verify($finding, $request->validated(), $request->file('evidence_verifikasi'), $this->actor($request));

        return back()->with('success', 'Proses verifikasi berhasil.');
    }

    private function actor(Request $request): string
    {
        return (string) ($request->user()->username ?? $request->user()->name);
    }
}
