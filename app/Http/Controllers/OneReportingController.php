<?php

namespace App\Http\Controllers;

use App\Http\Resources\OdrOutstandingResource;
use App\Models\FleetMasterUnit;
use App\Models\MppUnitPhoto;
use App\Models\OdrAssignment;
use App\Models\User;
use App\Services\OdrComplianceDashboardService;
use App\Support\MenuAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OneReportingController extends Controller
{
    public function __construct(private OdrComplianceDashboardService $complianceDashboard) {}

    private function commonProps(): array
    {
        return [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_reporting_menu'),
        ];
    }

    // ── 1. Dashboard Compliance ──────────────────────────────────────────────────
    public function dashboard(Request $request): Response
    {
        Gate::authorize('access-compliance-report');

        $filters = [
            'tanggal' => $request->date('tanggal')?->toDateString() ?? now()->toDateString(),
            'shift' => in_array($request->query('shift'), OdrAssignment::SHIFTS, true) ? $request->query('shift') : '',
            'area' => trim((string) $request->query('area', '')),
        ];
        [$tanggal, $shift] = [$filters['tanggal'], $filters['shift']];

        return Inertia::render('OneReporting/Dashboard', array_merge($this->commonProps(), [
            'filters' => $filters,
            'summary' => $this->complianceDashboard->summary($tanggal, $shift),
            'dailyTrend' => $this->complianceDashboard->dailyTrend($shift),
            'perArea' => $this->complianceDashboard->perArea($tanggal, $shift),
            'perPengawas' => $this->complianceDashboard->perPengawas($tanggal, $shift),
            'topPengawas' => $this->complianceDashboard->topPengawas($tanggal, $shift),
            'lowestAreas' => $this->complianceDashboard->lowestAreas($tanggal, $shift),
            'outstanding' => OdrOutstandingResource::collection($this->complianceDashboard->outstanding($tanggal, $shift, $filters['area']))->resolve(),
            'areaOptions' => $this->complianceDashboard->areaOptions(),
            'shiftOptions' => OdrAssignment::SHIFTS,
        ]));
    }

    // ── 5. OJK (Observasi Jaminan Safety / Upload Unit Photos) ──────────────────
    public function ojk(Request $request): Response
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $shift = $request->input('shift', 'D');

        ['unitGrid' => $unitGrid, 'metrics' => $metrics] = $this->ojkBoard($tanggal, $shift);

        return Inertia::render('OneReporting/OjkMonitoring', array_merge($this->commonProps(), [
            'filters' => [
                'tanggal' => $tanggal,
                'shift' => $shift,
            ],
            'unitGrid' => $unitGrid,
            'metrics' => $metrics,
        ]));
    }

    /**
     * Grid unit + foto OJK untuk satu tanggal/shift (dipakai halaman web dan API mobile).
     */
    private function ojkBoard(string $tanggal, string $shift, ?User $viewer = null): array
    {
        // Unit OJK = hauler aktif di master fleet (bukan daftar contoh/odr_master_units).
        $allUnits = FleetMasterUnit::where('is_active', true)
            ->where('unit_type', 'hauler')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->pluck('code')
            ->unique()
            ->values()
            ->all();

        $photos = MppUnitPhoto::where('jenis', 'OJK')
            ->where('tanggal', $tanggal)
            ->where('shift', $shift)
            ->get()
            ->keyBy(fn($p) => strtoupper(trim($p->unit_code)));

        $unitGrid = collect($allUnits)->map(function ($unitCode) use ($photos, $viewer) {
            $p = $photos->get(strtoupper(trim($unitCode)));
            return [
                'unit_code' => $unitCode,
                'has_photo' => !is_null($p),
                'photo' => $p ? [
                    'id' => $p->id,
                    'url' => str_starts_with($p->url, 'http') ? $p->url : asset('storage/' . $p->url),
                    'operator_nama' => $p->nama ?? 'Operator Unit',
                    'created_at' => $p->created_at->format('H:i'),
                ] + ($viewer ? ['can_delete' => $viewer->isAdmin() || $p->created_by === $viewer->id] : []) : null,
            ];
        })->sortByDesc(fn($u) => $u['has_photo'])->values();

        // Hanya foto milik unit yang tampil di daftar (foto kode lama tidak ikut terhitung).
        $uploadedCount = $unitGrid->where('has_photo', true)->count();
        $totalUnits = count($allUnits);

        return [
            'unitGrid' => $unitGrid,
            'metrics' => [
                'totalUploaded' => $uploadedCount,
                'totalRemaining' => $totalUnits - $uploadedCount,
                'totalUnits' => $totalUnits,
            ],
        ];
    }

    private function ensureOjkAccess(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && ($user->isGroupLeader() || $user->isAdmin()), 403, 'Menu ini hanya untuk Group Leader.');
    }

    /**
     * API mobile: monitoring foto OJK per tanggal/shift (?tanggal=Y-m-d&shift=D|N).
     */
    public function ojkApi(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->ensureOjkAccess($request);
        $request->validate([
            'tanggal' => 'nullable|date_format:Y-m-d',
            'shift' => 'nullable|in:D,N',
        ]);

        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $shift = $request->input('shift', 'D');

        return response()->json(['tanggal' => $tanggal, 'shift' => $shift] + $this->ojkBoard($tanggal, $shift, $request->user()));
    }

    /**
     * API mobile: unggah foto OJK (aturan sama dengan uploadOjk web).
     */
    public function ojkUploadApi(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->ensureOjkAccess($request);

        $this->uploadOjk($request);

        return response()->json(['message' => 'Foto OJK berhasil diunggah.']);
    }

    /**
     * API mobile: hapus foto OJK. Berbeda dari web, hanya pengunggahnya (atau admin) yang boleh menghapus.
     */
    public function ojkDestroyApi(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $this->ensureOjkAccess($request);

        $photo = MppUnitPhoto::where('jenis', 'OJK')->findOrFail($id);
        abort_unless($request->user()->isAdmin() || $photo->created_by === $request->user()->id, 403, 'Anda hanya dapat menghapus foto yang Anda unggah.');

        $this->deleteOjk($id);

        return response()->json(['message' => 'Foto OJK berhasil dihapus.']);
    }

    public function uploadOjk(Request $request)
    {
        $request->validate([
            'unit_code' => 'required|string',
            'tanggal' => 'required|date',
            'shift' => 'required|in:D,N',
            'foto' => 'required|image|max:10240',
        ]);

        $path = $request->file('foto')->store('uploads/ojk', 'public');

        MppUnitPhoto::create([
            'jenis' => 'OJK',
            'unit_code' => $request->unit_code,
            'tanggal' => $request->tanggal,
            'shift' => $request->shift,
            'nama' => $request->nama_operator ?? 'Operator Unit',
            'url' => $path,
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Foto OJK berhasil diupload');
    }

    public function deleteOjk(int $id)
    {
        $photo = MppUnitPhoto::findOrFail($id);
        if ($photo->url && Storage::disk('public')->exists($photo->url)) {
            Storage::disk('public')->delete($photo->url);
        }
        $photo->delete();

        return redirect()->back()->with('success', 'Foto OJK berhasil dihapus');
    }
}
