<?php

namespace App\Http\Controllers;

use App\Models\MppUnitPhoto;
use App\Models\OdrAssignment;
use App\Models\OdrMasterAktivitas;
use App\Models\OdrMasterArea;
use App\Models\OdrMasterUnit;
use App\Support\MenuAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OneReportingController extends Controller
{
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
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $shift = $request->input('shift', '');
        $area = $request->input('area', '');

        $query = OdrAssignment::query()->where('tanggal', $tanggal);
        if ($shift) $query->where('shift', $shift);
        if ($area) $query->where('area', $area);

        $assignments = $query->get();

        $totalAssignment = $assignments->count();
        $totalSelesai = $assignments->where('status', 'selesai')->count();
        $totalProgress = $assignments->where('status', 'progress')->count();
        $totalBelum = $totalAssignment - $totalSelesai - $totalProgress;
        $persentase = $totalAssignment > 0 ? round(($totalSelesai / $totalAssignment) * 100, 1) : 0;

        // 7-day trend
        $harian = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl = date('Y-m-d', strtotime("-$i days"));
            $qDaily = OdrAssignment::query()->where('tanggal', $tgl);
            if ($shift) $qDaily->where('shift', $shift);

            $tot = $qDaily->count();
            $sel = (clone $qDaily)->where('status', 'selesai')->count();
            $harian[] = [
                'tgl' => date('d/m', strtotime($tgl)),
                'selesai' => $sel,
                'total' => $tot,
            ];
        }

        // Compliance Per Area
        $qArea = OdrAssignment::query()->where('tanggal', $tanggal);
        if ($shift) $qArea->where('shift', $shift);
        $areaStats = $qArea->select('area', DB::raw('COUNT(*) as total'), DB::raw("SUM(status = 'selesai') as selesai"))
            ->groupBy('area')->orderBy('area')->get();

        // Top Compliance Pengawas
        $qPengawas = OdrAssignment::query()->where('tanggal', $tanggal);
        if ($shift) $qPengawas->where('shift', $shift);
        $topPengawas = $qPengawas->select('pengawas', DB::raw('COUNT(*) as assignment'), DB::raw("SUM(status = 'selesai') as selesai"))
            ->groupBy('pengawas')
            ->get()
            ->map(function ($row) {
                $row->persen = $row->assignment > 0 ? round(($row->selesai / $row->assignment) * 100, 1) : 0;
                return $row;
            })
            ->sortByDesc('persen')
            ->values()
            ->take(10);

        // Area Lowest
        $areaLow = $areaStats->map(function ($row) {
            $row->persen = $row->total > 0 ? round(($row->selesai / $row->total) * 100, 1) : 0;
            return $row;
        })->sortBy('persen')->values()->take(10);

        // Outstanding Activity (Belum dilaksanakan)
        $qOutstanding = OdrAssignment::query()->where('tanggal', $tanggal)->where('status', 'belum');
        if ($shift) $qOutstanding->where('shift', $shift);
        if ($area) $qOutstanding->where('area', $area);
        $outstanding = $qOutstanding->orderBy('area')->orderBy('pengawas')->get();

        $allAreas = OdrMasterArea::where('aktif', true)->pluck('nama')->toArray();
        if (empty($allAreas)) {
            $allAreas = OdrAssignment::distinct()->pluck('area')->filter()->values()->toArray();
        }

        return Inertia::render('OneReporting/Dashboard', array_merge($this->commonProps(), [
            'filters' => [
                'tanggal' => $tanggal,
                'shift' => $shift,
                'area' => $area,
            ],
            'metrics' => [
                'totalAssignment' => $totalAssignment,
                'totalSelesai' => $totalSelesai,
                'totalProgress' => $totalProgress,
                'totalBelum' => $totalBelum,
                'persentase' => $persentase,
            ],
            'chartHarian' => $harian,
            'areaStats' => $areaStats,
            'topPengawas' => $topPengawas,
            'areaLow' => $areaLow,
            'outstanding' => $outstanding,
            'allAreas' => $allAreas,
        ]));
    }

    // ── 2. Daily Compliance ──────────────────────────────────────────────────────
    public function daily(Request $request): Response
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $shift = $request->input('shift', '');
        $area = $request->input('area', '');
        $status = $request->input('status', '');

        $query = OdrAssignment::query()->where('tanggal', $tanggal);
        if ($shift) $query->where('shift', $shift);
        if ($area) $query->where('area', $area);
        if ($status) $query->where('status', $status);

        $rows = $query->orderBy('area')->orderBy('pengawas')->get()->map(function ($row) {
            $tasks = json_decode($row->aktivitas, true) ?? [];
            if (!is_array($tasks)) $tasks = $row->aktivitas ? [$row->aktivitas] : [];
            
            $dones = json_decode($row->task_done, true) ?? [];
            if (!is_array($dones)) $dones = array_fill(0, count($tasks), false);

            $doneCount = count(array_filter($dones));
            $totalTasks = count($tasks);
            $pct = $totalTasks > 0 ? round(($doneCount / $totalTasks) * 100, 1) : 0;

            $computedStatus = $totalTasks === 0 ? 'belum' : ($doneCount === 0 ? 'belum' : ($doneCount === $totalTasks ? 'selesai' : 'progress'));
            
            if ($row->status !== $computedStatus) {
                $row->status = $computedStatus;
                $row->save();
            }

            return [
                'id' => $row->id,
                'tanggal' => $row->tanggal,
                'shift' => $row->shift,
                'area' => $row->area,
                'unit' => $row->unit,
                'pengawas' => $row->pengawas,
                'tasks' => $tasks,
                'task_done' => $dones,
                'keterangan' => $row->keterangan,
                'status' => $computedStatus,
                'pct' => $pct,
                'doneCount' => $doneCount,
                'totalTasks' => $totalTasks,
            ];
        });

        $allAreas = OdrMasterArea::where('aktif', true)->pluck('nama')->toArray();
        $allUnits = OdrMasterUnit::where('aktif', true)->pluck('nama')->toArray();
        $allAktivitas = OdrMasterAktivitas::where('aktif', true)->pluck('nama')->toArray();

        return Inertia::render('OneReporting/DailyCompliance', array_merge($this->commonProps(), [
            'filters' => [
                'tanggal' => $tanggal,
                'shift' => $shift,
                'area' => $area,
                'status' => $status,
            ],
            'assignments' => $rows,
            'allAreas' => $allAreas,
            'allUnits' => $allUnits,
            'allAktivitas' => $allAktivitas,
        ]));
    }

    public function updateTask(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:odr_assignments,id',
            'task_index' => 'required|integer',
            'value' => 'required|boolean',
        ]);

        $assignment = OdrAssignment::findOrFail($request->id);
        $tasks = json_decode($assignment->aktivitas, true) ?? [];
        if (!is_array($tasks)) $tasks = $assignment->aktivitas ? [$assignment->aktivitas] : [];

        $dones = json_decode($assignment->task_done, true) ?? [];
        if (!is_array($dones)) $dones = array_fill(0, count($tasks), false);

        $taskIndex = $request->task_index;
        if (isset($dones[$taskIndex])) {
            $dones[$taskIndex] = (bool) $request->value;
        }

        $doneCount = count(array_filter($dones));
        $totalTasks = count($tasks);
        $computedStatus = $totalTasks === 0 ? 'belum' : ($doneCount === 0 ? 'belum' : ($doneCount === $totalTasks ? 'selesai' : 'progress'));

        $assignment->task_done = json_encode($dones);
        $assignment->status = $computedStatus;
        $assignment->save();

        if ($request->header('X-Inertia') || ! $request->wantsJson()) {
            return redirect()->back();
        }

        return response()->json([
            'success' => true,
            'pct' => $totalTasks > 0 ? round(($doneCount / $totalTasks) * 100, 1) : 0,
            'status' => $computedStatus,
            'done' => $doneCount,
            'total' => $totalTasks,
        ]);
    }

    public function storeAssignment(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'shift' => 'required|string',
            'area' => 'required|string',
            'unit' => 'required|string',
            'pengawas' => 'required|string',
            'tasks' => 'required|array',
        ]);

        $tasks = array_values($request->tasks);
        $dones = array_fill(0, count($tasks), false);

        OdrAssignment::create([
            'tanggal' => $request->tanggal,
            'shift' => $request->shift,
            'area' => $request->area,
            'unit' => $request->unit,
            'pengawas' => $request->pengawas,
            'aktivitas' => json_encode($tasks),
            'task_done' => json_encode($dones),
            'keterangan' => $request->keterangan ?? '',
            'status' => 'belum',
        ]);

        return redirect()->back()->with('success', 'Assignment berhasil ditambahkan');
    }

    public function deleteAssignment(int $id)
    {
        OdrAssignment::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Assignment berhasil dihapus');
    }

    // ── 3. Laporan Compliance ────────────────────────────────────────────────────
    public function laporan(Request $request): Response
    {
        $tab = $request->input('tab', 'laporan');
        $tglDari = $request->input('tgl_dari', date('Y-m-01'));
        $tglSampai = $request->input('tgl_sampai', date('Y-m-d'));
        $area = $request->input('area', '');
        $pengawas = $request->input('pengawas', '');
        $status = $request->input('status', '');
        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));

        $query = OdrAssignment::query()->whereBetween('tanggal', [$tglDari, $tglSampai]);
        if ($area) $query->where('area', $area);
        if ($pengawas) $query->where('pengawas', 'like', "%{$pengawas}%");
        if ($status) $query->where('status', $status);

        $laporanRows = [];
        $rekapHarian = [];
        $rekapBulanan = [];

        if ($tab === 'laporan') {
            $laporanRows = $query->orderBy('tanggal', 'desc')->orderBy('area')->get()->map(function ($row) {
                $tasks = json_decode($row->aktivitas, true) ?? [];
                if (!is_array($tasks)) $tasks = $row->aktivitas ? [$row->aktivitas] : [];
                $dones = json_decode($row->task_done, true) ?? [];
                if (!is_array($dones)) $dones = array_fill(0, count($tasks), false);
                $doneCount = count(array_filter($dones));
                $totalTasks = count($tasks);
                return [
                    'id' => $row->id,
                    'tanggal' => $row->tanggal,
                    'area' => $row->area,
                    'unit' => $row->unit,
                    'pengawas' => $row->pengawas,
                    'tasks' => $tasks,
                    'task_done' => $dones,
                    'pct' => $totalTasks > 0 ? round(($doneCount / $totalTasks) * 100, 1) : 0,
                    'status' => $row->status,
                    'keterangan' => $row->keterangan,
                ];
            });
        } elseif ($tab === 'rekap_harian') {
            $raw = $query->orderBy('tanggal', 'desc')->get();
            $grouped = [];
            foreach ($raw as $r) {
                $tgl = $r->tanggal;
                if (!isset($grouped[$tgl])) $grouped[$tgl] = ['total' => 0, 'selesai' => 0, 'progress' => 0, 'belum' => 0, 'pctSum' => 0];
                $grouped[$tgl]['total']++;
                $grouped[$tgl][$r->status === 'selesai' ? 'selesai' : ($r->status === 'progress' ? 'progress' : 'belum')]++;

                $tasks = json_decode($r->aktivitas, true) ?? [];
                $dones = json_decode($r->task_done, true) ?? [];
                $total = count($tasks);
                $pct = $total > 0 ? (count(array_filter($dones)) / $total * 100) : 0;
                $grouped[$tgl]['pctSum'] += $pct;
            }

            foreach ($grouped as $tgl => $g) {
                $rekapHarian[] = [
                    'tanggal' => $tgl,
                    'total' => $g['total'],
                    'selesai' => $g['selesai'],
                    'progress' => $g['progress'],
                    'belum' => $g['belum'],
                    'pct' => $g['total'] > 0 ? round($g['pctSum'] / $g['total'], 1) : 0,
                ];
            }
        } elseif ($tab === 'rekap_bulanan') {
            $bulananRows = OdrAssignment::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();
            $byHari = []; $byPengawas = []; $byArea = [];
            foreach ($bulananRows as $r) {
                $hari = (int) date('d', strtotime($r->tanggal));
                $tasks = json_decode($r->aktivitas, true) ?? [];
                $dones = json_decode($r->task_done, true) ?? [];
                $total = count($tasks);
                $pct = $total > 0 ? (count(array_filter($dones)) / $total * 100) : 0;

                $byHari[$hari][] = $pct;
                $byPengawas[$r->pengawas][] = $pct;
                $byArea[$r->area][] = $pct;
            }
            ksort($byHari);

            $rekapBulanan = [
                'harian' => collect($byHari)->map(fn($v, $k) => ['hari' => $k, 'pct' => round(array_sum($v)/count($v), 1)])->values(),
                'pengawas' => collect($byPengawas)->map(fn($v, $k) => ['pengawas' => $k, 'pct' => round(array_sum($v)/count($v), 1)])->sortByDesc('pct')->values()->take(12),
                'area' => collect($byArea)->map(fn($v, $k) => ['area' => $k, 'pct' => round(array_sum($v)/count($v), 1)])->sortByDesc('pct')->values(),
            ];
        }

        $allAreas = OdrMasterArea::where('aktif', true)->pluck('nama')->toArray();

        return Inertia::render('OneReporting/LaporanCompliance', array_merge($this->commonProps(), [
            'tab' => $tab,
            'filters' => [
                'tgl_dari' => $tglDari,
                'tgl_sampai' => $tglSampai,
                'area' => $area,
                'pengawas' => $pengawas,
                'status' => $status,
                'bulan' => $bulan,
                'tahun' => $tahun,
            ],
            'laporanRows' => $laporanRows,
            'rekapHarian' => $rekapHarian,
            'rekapBulanan' => $rekapBulanan,
            'allAreas' => $allAreas,
        ]));
    }

    public function exportCsv(Request $request)
    {
        $tglDari = $request->input('tgl_dari', date('Y-m-01'));
        $tglSampai = $request->input('tgl_sampai', date('Y-m-d'));
        $area = $request->input('area', '');

        $query = OdrAssignment::query()->whereBetween('tanggal', [$tglDari, $tglSampai]);
        if ($area) $query->where('area', $area);

        $data = $query->orderBy('tanggal', 'desc')->get();

        $filename = "laporan_compliance_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type" => "text/csv; charset=utf-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['No', 'Tanggal', 'Shift', 'Area', 'Unit', 'Pengawas', 'Status', 'Catatan']);

            foreach ($data as $i => $row) {
                fputcsv($file, [
                    $i + 1,
                    $row->tanggal,
                    $row->shift,
                    $row->area,
                    $row->unit,
                    $row->pengawas,
                    $row->status,
                    $row->keterangan,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── 4. Master Data Compliance ────────────────────────────────────────────────
    public function master(Request $request): Response
    {
        $tab = $request->input('tab', 'area');

        $areas = OdrMasterArea::orderBy('nama')->get();
        $units = OdrMasterUnit::orderBy('area')->orderBy('nama')->get();
        $aktivitas = OdrMasterAktivitas::orderBy('kategori')->orderBy('nama')->get();

        return Inertia::render('OneReporting/MasterCompliance', array_merge($this->commonProps(), [
            'tab' => $tab,
            'areas' => $areas,
            'units' => $units,
            'aktivitas' => $aktivitas,
        ]));
    }

    public function storeArea(Request $request)
    {
        $request->validate(['nama' => 'required|string|unique:odr_master_area,nama']);
        OdrMasterArea::create(['nama' => $request->nama, 'keterangan' => $request->keterangan]);
        return redirect()->back()->with('success', 'Master Area berhasil ditambahkan');
    }

    public function updateArea(Request $request, int $id)
    {
        $request->validate(['nama' => 'required|string']);
        $area = OdrMasterArea::findOrFail($id);
        $area->update([
            'nama' => $request->nama,
            'keterangan' => $request->keterangan,
            'aktif' => (bool)$request->aktif,
        ]);
        return redirect()->back()->with('success', 'Master Area berhasil diperbarui');
    }

    public function deleteArea(int $id)
    {
        OdrMasterArea::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Master Area berhasil dihapus');
    }

    public function storeUnit(Request $request)
    {
        $request->validate(['nama' => 'required|string']);
        OdrMasterUnit::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'area' => $request->area,
            'keterangan' => $request->keterangan,
        ]);
        return redirect()->back()->with('success', 'Master Unit berhasil ditambahkan');
    }

    public function updateUnit(Request $request, int $id)
    {
        $request->validate(['nama' => 'required|string']);
        $unit = OdrMasterUnit::findOrFail($id);
        $unit->update([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'area' => $request->area,
            'keterangan' => $request->keterangan,
            'aktif' => (bool)$request->aktif,
        ]);
        return redirect()->back()->with('success', 'Master Unit berhasil diperbarui');
    }

    public function deleteUnit(int $id)
    {
        OdrMasterUnit::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Master Unit berhasil dihapus');
    }

    public function storeAktivitas(Request $request)
    {
        $request->validate(['nama' => 'required|string']);
        OdrMasterAktivitas::create([
            'nama' => $request->nama,
            'kategori' => $request->kategori,
            'keterangan' => $request->keterangan,
        ]);
        return redirect()->back()->with('success', 'Master Aktivitas berhasil ditambahkan');
    }

    public function updateAktivitas(Request $request, int $id)
    {
        $request->validate(['nama' => 'required|string']);
        $akt = OdrMasterAktivitas::findOrFail($id);
        $akt->update([
            'nama' => $request->nama,
            'kategori' => $request->kategori,
            'keterangan' => $request->keterangan,
            'aktif' => (bool)$request->aktif,
        ]);
        return redirect()->back()->with('success', 'Master Aktivitas berhasil diperbarui');
    }

    public function deleteAktivitas(int $id)
    {
        OdrMasterAktivitas::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Master Aktivitas berhasil dihapus');
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
