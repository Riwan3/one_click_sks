<?php

namespace App\Http\Controllers;

use App\Http\Resources\OdrOutstandingResource;
use App\Models\MppUnitPhoto;
use App\Models\OdrAssignment;
use App\Models\OdrMasterUnit;
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

        $defaultUnits = ['HD785-012', 'PC2000-004', 'D375-007', 'WA500-009', 'GD675-002', 'HD785-018', 'PC2000-011', 'D375-014', 'DT-101', 'DT-102'];
        $masterUnits = OdrMasterUnit::where('aktif', true)->pluck('nama')->toArray();
        $allUnits = array_values(array_unique(array_merge($defaultUnits, $masterUnits)));

        $photos = MppUnitPhoto::where('jenis', 'OJK')
            ->where('tanggal', $tanggal)
            ->where('shift', $shift)
            ->get()
            ->keyBy(fn($p) => strtoupper(trim($p->unit_code)));

        $unitGrid = collect($allUnits)->map(function ($unitCode) use ($photos) {
            $p = $photos->get(strtoupper(trim($unitCode)));
            return [
                'unit_code' => $unitCode,
                'has_photo' => !is_null($p),
                'photo' => $p ? [
                    'id' => $p->id,
                    'url' => str_starts_with($p->url, 'http') ? $p->url : asset('storage/' . $p->url),
                    'operator_nama' => $p->nama ?? 'Operator Unit',
                    'created_at' => $p->created_at->format('H:i'),
                ] : null,
            ];
        })->sortByDesc(fn($u) => $u['has_photo'])->values();

        $uploadedCount = $photos->count();
        $totalUnits = count($allUnits);

        return Inertia::render('OneReporting/OjkMonitoring', array_merge($this->commonProps(), [
            'filters' => [
                'tanggal' => $tanggal,
                'shift' => $shift,
            ],
            'unitGrid' => $unitGrid,
            'metrics' => [
                'totalUploaded' => $uploadedCount,
                'totalRemaining' => $totalUnits - $uploadedCount,
                'totalUnits' => $totalUnits,
            ],
        ]));
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
