<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePemanggilanKaryawanRequest;
use App\Http\Requests\UpdatePemanggilanKaryawanRequest;
use App\Http\Resources\PemanggilanKaryawanResource;
use App\Models\Employee;
use App\Models\PemanggilanKaryawan;
use App\Models\Setting;
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
        $dateFrom = trim((string) $request->string('date_from', now()->startOfMonth()->toDateString()));
        $dateTo = trim((string) $request->string('date_to', now()->toDateString()));
        $page = max(1, (int) $request->integer('page', 1));

        $siteCode = Setting::getValue('pemanggilan_karyawan_site_code', 'ADW') ?: 'ADW';
        PemanggilanKaryawan::updateSiteCode($siteCode);

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
            'canManage' => ! $request->user()?->isManagement(),
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        return Inertia::render('OnePower/PemanggilanKaryawan/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function store(StorePemanggilanKaryawanRequest $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->pemanggilanKaryawanService->create($request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/pemanggilan-karyawan')->with('success', 'Data pemanggilan karyawan berhasil dibuat.');
    }

    public function show(PemanggilanKaryawan $pemanggilanKaryawan): Response
    {
        $pemanggilanKaryawan->load(['employee', 'creator', 'updater']);

        return Inertia::render('OnePower/PemanggilanKaryawan/Print', [
            'pemanggilan' => (new PemanggilanKaryawanResource($pemanggilanKaryawan))->resolve(),
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function edit(Request $request, PemanggilanKaryawan $pemanggilanKaryawan): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        $pemanggilanKaryawan->load(['employee', 'creator', 'updater']);

        return Inertia::render('OnePower/PemanggilanKaryawan/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetItem' => (new PemanggilanKaryawanResource($pemanggilanKaryawan))->resolve(),
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function update(UpdatePemanggilanKaryawanRequest $request, PemanggilanKaryawan $pemanggilanKaryawan): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->pemanggilanKaryawanService->update($pemanggilanKaryawan, $request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/pemanggilan-karyawan')->with('success', 'Data pemanggilan karyawan berhasil diperbarui.');
    }

    public function destroy(Request $request, PemanggilanKaryawan $pemanggilanKaryawan): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->pemanggilanKaryawanService->delete($pemanggilanKaryawan);

        return redirect()->back()->with('success', 'Data pemanggilan karyawan berhasil dihapus.');
    }

    public function nextNumber(Request $request): JsonResponse
    {
        $tanggal = trim((string) $request->string('tanggal'));

        return response()->json([
            'nomor_surat' => PemanggilanKaryawan::nextNomorSurat($tanggal ?: null),
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
            'dept' => ['nullable', 'string', 'max:150'],
            'noDokumen' => ['required', 'string', 'max:100'],
            'revisi' => ['nullable', 'string', 'max:50'],
            'tglEfektif' => ['nullable', 'date'],
        ]);

        $siteCode = strtoupper(trim($validated['siteCode']));
        $lokasi = trim($validated['lokasi'] ?? 'Wara');
        $dept = trim($validated['dept'] ?? 'JOB SITE ADARO - OPERATIONAL DEPARTMENT');
        $noDokumen = trim($validated['noDokumen']);
        $revisi = trim($validated['revisi'] ?? '00');
        $tglEfektif = $validated['tglEfektif'] ?? '2022-09-12';

        Setting::setValue('pemanggilan_karyawan_site_code', $siteCode);
        Setting::setValue('pemanggilan_karyawan_lokasi', $lokasi);
        Setting::setValue('pemanggilan_karyawan_dept', $dept);
        Setting::setValue('pemanggilan_karyawan_no_dokumen', $noDokumen);
        Setting::setValue('pemanggilan_karyawan_revisi', $revisi);
        Setting::setValue('pemanggilan_karyawan_tgl_efektif', $tglEfektif);

        PemanggilanKaryawan::updateSiteCode($siteCode);

        return redirect()->back()->with('success', 'Pengaturan format dokumen & no. surat pemanggilan berhasil dikunci.');
    }

    private function getDocumentSettings(): array
    {
        return [
            'siteCode' => Setting::getValue('pemanggilan_karyawan_site_code', 'ADW') ?: 'ADW',
            'lokasi' => Setting::getValue('pemanggilan_karyawan_lokasi', 'Wara') ?: 'Wara',
            'dept' => Setting::getValue('pemanggilan_karyawan_dept', 'JOB SITE ADARO - OPERATIONAL DEPARTMENT') ?: 'JOB SITE ADARO - OPERATIONAL DEPARTMENT',
            'noDokumen' => Setting::getValue('pemanggilan_karyawan_no_dokumen', 'PPA-ADRO-F-OPR-01') ?: 'PPA-ADRO-F-OPR-01',
            'revisi' => Setting::getValue('pemanggilan_karyawan_revisi', '00') ?: '00',
            'tglEfektif' => Setting::getValue('pemanggilan_karyawan_tgl_efektif', '2022-09-12') ?: '2022-09-12',
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
