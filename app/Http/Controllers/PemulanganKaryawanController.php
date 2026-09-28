<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePemulanganKaryawanRequest;
use App\Http\Requests\UpdatePemulanganKaryawanRequest;
use App\Http\Resources\PemulanganKaryawanResource;
use App\Models\Employee;
use App\Models\PemulanganKaryawan;
use App\Models\Setting;
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
        $dateFrom = trim((string) $request->string('date_from', now()->startOfMonth()->toDateString()));
        $dateTo = trim((string) $request->string('date_to', now()->toDateString()));
        $page = max(1, (int) $request->integer('page', 1));

        $siteCode = Setting::getValue('pemulangan_karyawan_site_code', 'ADW') ?: 'ADW';
        PemulanganKaryawan::updateSiteCode($siteCode);

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
            'canManage' => ! $request->user()?->isManagement(),
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        return Inertia::render('OnePower/PemulanganKaryawan/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function store(StorePemulanganKaryawanRequest $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->pemulanganKaryawanService->create($request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/pemulangan-karyawan')->with('success', 'Data pemulangan karyawan berhasil dibuat.');
    }

    public function edit(Request $request, PemulanganKaryawan $pemulanganKaryawan): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        $pemulanganKaryawan->load(['employee', 'creator', 'updater']);

        return Inertia::render('OnePower/PemulanganKaryawan/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetItem' => (new PemulanganKaryawanResource($pemulanganKaryawan))->resolve(),
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function update(UpdatePemulanganKaryawanRequest $request, PemulanganKaryawan $pemulanganKaryawan): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->pemulanganKaryawanService->update($pemulanganKaryawan, $request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/pemulangan-karyawan')->with('success', 'Data pemulangan karyawan berhasil diperbarui.');
    }

    public function destroy(Request $request, PemulanganKaryawan $pemulanganKaryawan): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->pemulanganKaryawanService->delete($pemulanganKaryawan);

        return redirect()->back()->with('success', 'Data pemulangan karyawan berhasil dihapus.');
    }

    public function show(PemulanganKaryawan $pemulanganKaryawan): Response
    {
        $pemulanganKaryawan->load(['employee', 'creator', 'updater']);

        return Inertia::render('OnePower/PemulanganKaryawan/Print', [
            'item' => (new PemulanganKaryawanResource($pemulanganKaryawan))->resolve(),
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function nextNumber(Request $request): JsonResponse
    {
        $tanggal = trim((string) $request->string('tanggal')) ?: date('Y-m-d');
        [$docRank, $noSurat] = PemulanganKaryawan::nextDocRank($tanggal);

        return response()->json([
            'doc_rank' => $docRank,
            'nomor_surat' => $noSurat,
        ]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || (! $user->isAdmin() && ! $user->isPimpinan())) {
            abort(403);
        }

        $validated = $request->validate([
            'siteCode' => ['required', 'string', 'max:20'],
            'lokasi' => ['nullable', 'string', 'max:100'],
            'noDokumen' => ['required', 'string', 'max:100'],
            'revisi' => ['nullable', 'string', 'max:50'],
            'tglEfektif' => ['nullable', 'date'],
        ]);

        $siteCode = strtoupper(trim($validated['siteCode']));
        $lokasi = trim($validated['lokasi'] ?? 'Tabalong');
        $noDokumen = trim($validated['noDokumen']);
        $revisi = trim($validated['revisi'] ?? '0');
        $tglEfektif = $validated['tglEfektif'] ?? '2023-05-18';

        Setting::setValue('pemulangan_karyawan_site_code', $siteCode);
        Setting::setValue('pemulangan_karyawan_lokasi', $lokasi);
        Setting::setValue('pemulangan_karyawan_no_dokumen', $noDokumen);
        Setting::setValue('pemulangan_karyawan_revisi', $revisi);
        Setting::setValue('pemulangan_karyawan_tgl_efektif', $tglEfektif);

        PemulanganKaryawan::updateSiteCode($siteCode);

        return redirect()->back()->with('success', 'Pengaturan format dokumen & no. surat pemulangan berhasil dikunci.');
    }

    private function getDocumentSettings(): array
    {
        return [
            'siteCode' => Setting::getValue('pemulangan_karyawan_site_code', 'ADW') ?: 'ADW',
            'lokasi' => Setting::getValue('pemulangan_karyawan_lokasi', 'Tabalong') ?: 'Tabalong',
            'noDokumen' => Setting::getValue('pemulangan_karyawan_no_dokumen', 'PPA-ADRO-F-SHE-29F') ?: 'PPA-ADRO-F-SHE-29F',
            'revisi' => Setting::getValue('pemulangan_karyawan_revisi', '0') ?: '0',
            'tglEfektif' => Setting::getValue('pemulangan_karyawan_tgl_efektif', '2023-05-18') ?: '2023-05-18',
        ];
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
