<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreValidasiIstirahatRequest;
use App\Http\Requests\UpdateValidasiIstirahatRequest;
use App\Http\Resources\ValidasiIstirahatResource;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\ValidasiIstirahat;
use App\Services\ValidasiIstirahatService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ValidasiIstirahatController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private ValidasiIstirahatService $validasiIstirahatService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from', now()->startOfMonth()->toDateString()));
        $dateTo = trim((string) $request->string('date_to', now()->toDateString()));
        $page = max(1, (int) $request->integer('page', 1));

        $siteCode = Setting::getValue('validasi_istirahat_site_code', 'ADW') ?: 'ADW';
        ValidasiIstirahat::updateSiteCode($siteCode);

        $items = $this->validasiIstirahatService->paginate($search, $dateFrom ?: null, $dateTo ?: null, $page, self::PER_PAGE);

        return Inertia::render('OnePower/ValidasiIstirahat/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'items' => ValidasiIstirahatResource::collection($items->items())->resolve(),
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

        return Inertia::render('OnePower/ValidasiIstirahat/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function store(StoreValidasiIstirahatRequest $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->validasiIstirahatService->create($request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/validasi-istirahat')->with('success', 'Validasi istirahat berhasil dibuat.');
    }

    public function edit(Request $request, ValidasiIstirahat $validasiIstirahat): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        $validasiIstirahat->load(['employee', 'creator', 'updater']);

        return Inertia::render('OnePower/ValidasiIstirahat/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetItem' => (new ValidasiIstirahatResource($validasiIstirahat))->resolve(),
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function update(UpdateValidasiIstirahatRequest $request, ValidasiIstirahat $validasiIstirahat): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->validasiIstirahatService->update($validasiIstirahat, $request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/validasi-istirahat')->with('success', 'Validasi istirahat berhasil diperbarui.');
    }

    public function destroy(Request $request, ValidasiIstirahat $validasiIstirahat): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->validasiIstirahatService->delete($validasiIstirahat);

        return redirect()->back()->with('success', 'Validasi istirahat berhasil dihapus.');
    }

    public function show(ValidasiIstirahat $validasiIstirahat): Response
    {
        $validasiIstirahat->load(['employee', 'creator', 'updater']);

        return Inertia::render('OnePower/ValidasiIstirahat/Print', [
            'item' => (new ValidasiIstirahatResource($validasiIstirahat))->resolve(),
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function nextNumber(Request $request): JsonResponse
    {
        $tanggal = trim((string) $request->string('tanggal')) ?: date('Y-m-d');
        [$docRank, $noSurat] = ValidasiIstirahat::nextDocRank($tanggal);

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
        $tglEfektif = $validated['tglEfektif'] ?? '2023-06-23';

        Setting::setValue('validasi_istirahat_site_code', $siteCode);
        Setting::setValue('validasi_istirahat_lokasi', $lokasi);
        Setting::setValue('validasi_istirahat_no_dokumen', $noDokumen);
        Setting::setValue('validasi_istirahat_revisi', $revisi);
        Setting::setValue('validasi_istirahat_tgl_efektif', $tglEfektif);

        ValidasiIstirahat::updateSiteCode($siteCode);

        return redirect()->back()->with('success', 'Pengaturan format dokumen & no. surat validasi istirahat berhasil dikunci.');
    }

    private function getDocumentSettings(): array
    {
        return [
            'siteCode' => Setting::getValue('validasi_istirahat_site_code', 'ADW') ?: 'ADW',
            'lokasi' => Setting::getValue('validasi_istirahat_lokasi', 'Tabalong') ?: 'Tabalong',
            'noDokumen' => Setting::getValue('validasi_istirahat_no_dokumen', 'PPA-ADRO-F-SHE-29G') ?: 'PPA-ADRO-F-SHE-29G',
            'revisi' => Setting::getValue('validasi_istirahat_revisi', '0') ?: '0',
            'tglEfektif' => Setting::getValue('validasi_istirahat_tgl_efektif', '2023-06-23') ?: '2023-06-23',
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
