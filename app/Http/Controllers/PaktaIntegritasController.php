<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaktaIntegritasRequest;
use App\Http\Requests\UpdatePaktaIntegritasRequest;
use App\Http\Resources\PaktaIntegritasResource;
use App\Models\Employee;
use App\Models\PaktaIntegritas;
use App\Models\Setting;
use App\Services\PaktaIntegritasService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaktaIntegritasController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private PaktaIntegritasService $paktaIntegritasService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from', now()->startOfMonth()->toDateString()));
        $dateTo = trim((string) $request->string('date_to', now()->toDateString()));
        $page = max(1, (int) $request->integer('page', 1));

        $siteCode = Setting::getValue('pakta_integritas_site_code', 'ADW') ?: 'ADW';
        PaktaIntegritas::updateSiteCode($siteCode);

        $items = $this->paktaIntegritasService->paginate($search, $dateFrom ?: null, $dateTo ?: null, $page, self::PER_PAGE);

        return Inertia::render('OnePower/PaktaIntegritas/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'items' => PaktaIntegritasResource::collection($items->items())->resolve(),
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

        return Inertia::render('OnePower/PaktaIntegritas/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
        ]);
    }

    public function store(StorePaktaIntegritasRequest $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->paktaIntegritasService->create($request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/pakta-integritas')->with('success', 'Pakta integritas berhasil dibuat.');
    }

    public function edit(Request $request, PaktaIntegritas $paktaIntegrita): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        $paktaIntegrita->load('employee');

        return Inertia::render('OnePower/PaktaIntegritas/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetItem' => new PaktaIntegritasResource($paktaIntegrita),
        ]);
    }

    public function update(UpdatePaktaIntegritasRequest $request, PaktaIntegritas $paktaIntegrita): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->paktaIntegritasService->update($paktaIntegrita, $request->validated());

        return redirect('/one-power/pembinaan/pakta-integritas')->with('success', 'Pakta integritas berhasil diperbarui.');
    }

    public function destroy(Request $request, PaktaIntegritas $paktaIntegrita): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->paktaIntegritasService->delete($paktaIntegrita);

        return redirect()->back()->with('success', 'Pakta integritas berhasil dihapus.');
    }

    public function show(PaktaIntegritas $paktaIntegrita): Response
    {
        $paktaIntegrita->load(['employee', 'creator']);

        return Inertia::render('OnePower/PaktaIntegritas/Print', [
            'item' => (new PaktaIntegritasResource($paktaIntegrita))->resolve(),
            'documentSettings' => $this->getDocumentSettings(),
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
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $count = $this->paktaIntegritasService->importFromExcel($request->file('file'), $request->user()?->id);

        return redirect('/one-power/pembinaan/pakta-integritas')
            ->with('success', "Berhasil import {$count} data Pakta Integritas.");
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = ['NRP', 'Nama Karyawan', 'Jabatan', 'Tanggal', 'Nomor Surat', 'Tempat', 'Pernyataan', 'Atasan Nama', 'Pimpinan Nama', 'CC', 'Arsip'];

        return response()->streamDownload(function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        }, 'pakta-integritas-template.csv', [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    public function nextNumber(Request $request): JsonResponse
    {
        $tanggal = trim((string) $request->string('tanggal'));

        if ($tanggal === '') {
            return response()->json(['nomor_surat' => '']);
        }

        $nomorSurat = PaktaIntegritas::nextNomorSurat($tanggal);

        return response()->json(['nomor_surat' => $nomorSurat]);
    }

    public function printBlank(PaktaIntegritas $paktaIntegrita): Response
    {
        $paktaIntegrita->load(['employee', 'creator']);

        return Inertia::render('OnePower/PaktaIntegritas/Print', [
            'item' => (new PaktaIntegritasResource($paktaIntegrita))->resolve(),
            'isBlank' => true,
            'documentSettings' => $this->getDocumentSettings(),
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
        $lokasi = trim($validated['lokasi'] ?? 'Tanjung');
        $noDokumen = trim($validated['noDokumen']);
        $revisi = trim($validated['revisi'] ?? '00');
        $tglEfektif = $validated['tglEfektif'] ?? '2022-09-12';

        Setting::setValue('pakta_integritas_site_code', $siteCode);
        Setting::setValue('pakta_integritas_lokasi', $lokasi);
        Setting::setValue('pakta_integritas_no_dokumen', $noDokumen);
        Setting::setValue('pakta_integritas_revisi', $revisi);
        Setting::setValue('pakta_integritas_tgl_efektif', $tglEfektif);

        PaktaIntegritas::updateSiteCode($siteCode);

        return redirect()->back()->with('success', 'Pengaturan format dokumen & no. surat pakta integritas berhasil dikunci.');
    }

    private function getDocumentSettings(): array
    {
        return [
            'siteCode' => Setting::getValue('pakta_integritas_site_code', 'ADW') ?: 'ADW',
            'lokasi' => Setting::getValue('pakta_integritas_lokasi', 'Tanjung') ?: 'Tanjung',
            'noDokumen' => Setting::getValue('pakta_integritas_no_dokumen', 'PPA-ADRO-F-PRO-07') ?: 'PPA-ADRO-F-PRO-07',
            'revisi' => Setting::getValue('pakta_integritas_revisi', '00') ?: '00',
            'tglEfektif' => Setting::getValue('pakta_integritas_tgl_efektif', '2022-09-12') ?: '2022-09-12',
        ];
    }
}
