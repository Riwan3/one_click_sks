<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBeritaAcaraRequest;
use App\Http\Requests\UpdateBeritaAcaraRequest;
use App\Http\Resources\BeritaAcaraResource;
use App\Models\BeritaAcara;
use App\Models\Employee;
use App\Models\Setting;
use App\Services\BeritaAcaraService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Inertia\Inertia;
use Inertia\Response;

class BeritaAcaraController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private BeritaAcaraService $beritaAcaraService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from', now()->startOfMonth()->toDateString()));
        $dateTo = trim((string) $request->string('date_to', now()->toDateString()));
        $page = max(1, (int) $request->integer('page', 1));

        $siteCode = Setting::getValue('berita_acara_site_code', 'ADW') ?: 'ADW';
        BeritaAcara::updateSiteCode($siteCode);

        $items = $this->beritaAcaraService->paginate($search, $dateFrom ?: null, $dateTo ?: null, $page, self::PER_PAGE);

        return Inertia::render('OnePower/BeritaAcara/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'items' => BeritaAcaraResource::collection($items->items())->resolve(),
            'pagination' => [
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
                'total' => $items->total(),
            ],
            'canManage' => ! $request->user()?->isManagement(),
            'documentSettings' => [
                'siteCode' => Setting::getValue('berita_acara_site_code', 'ADW') ?: 'ADW',
                'lokasi' => Setting::getValue('berita_acara_lokasi', 'Wara') ?: 'Wara',
                'noDokumen' => Setting::getValue('berita_acara_no_dokumen', 'PPA-ADRO-F-SHE-24D') ?: 'PPA-ADRO-F-SHE-24D',
                'revisi' => Setting::getValue('berita_acara_revisi', '0') ?: '0',
                'tglEfektif' => Setting::getValue('berita_acara_tgl_efektif', '2022-04-07') ?: '2022-04-07',
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        $defaultTanggal = now()->toDateString();
        $defaultNomorSurat = BeritaAcara::nextNomorSurat($defaultTanggal);

        return Inertia::render('OnePower/BeritaAcara/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'defaultValues' => [
                'nomorSurat' => $defaultNomorSurat,
                'tanggal' => $defaultTanggal,
            ],
        ]);
    }

    public function store(StoreBeritaAcaraRequest $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->beritaAcaraService->create($request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/berita-acara')->with('success', 'Berita acara berhasil dibuat.');
    }

    public function edit(Request $request, BeritaAcara $beritaAcara): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        $beritaAcara->load(['employee', 'diketahuiOleh']);

        return Inertia::render('OnePower/BeritaAcara/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetItem' => (new BeritaAcaraResource($beritaAcara))->resolve(),
        ]);
    }

    public function update(UpdateBeritaAcaraRequest $request, BeritaAcara $beritaAcara): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->beritaAcaraService->update($beritaAcara, $request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/berita-acara')->with('success', 'Berita acara berhasil diperbarui.');
    }

    public function destroy(Request $request, BeritaAcara $beritaAcara): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->beritaAcaraService->delete($beritaAcara);

        return redirect()->back()->with('success', 'Berita acara berhasil dihapus.');
    }

    public function import(Request $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $result = $this->beritaAcaraService->importFromExcel($request->file('file'), $request->user()?->id);

        $message = "Berhasil mengimport {$result['imported']} berita acara.";

        if ($result['invalidDates'] > 0) {
            $message .= " {$result['invalidDates']} baris memiliki tanggal tidak valid dan menggunakan tanggal hari ini — mohon periksa kembali.";
        }

        return redirect('/one-power/pembinaan/berita-acara')->with('success', $message);
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = ['nrp', 'nama_karyawan', 'jabatan', 'tanggal', 'detail', 'pengawas'];

        return ResponseFacade::streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, ['10001', 'NAMA CONTOH', 'OPERATOR HD785', '2026-08-26', 'Contoh kronologi kejadian...', '654321']);
            fclose($handle);
        }, 'template_berita_acara.csv', ['Content-Type' => 'text/csv']);
    }

    public function show(BeritaAcara $beritaAcara): Response
    {
        $beritaAcara->load(['employee', 'diketahuiOleh']);

        return Inertia::render('OnePower/BeritaAcara/Print', [
            'item' => (new BeritaAcaraResource($beritaAcara))->resolve(),
            'documentSettings' => [
                'siteCode' => Setting::getValue('berita_acara_site_code', 'ADW') ?: 'ADW',
                'lokasi' => Setting::getValue('berita_acara_lokasi', 'Wara') ?: 'Wara',
                'noDokumen' => Setting::getValue('berita_acara_no_dokumen', 'PPA-ADRO-F-SHE-24D') ?: 'PPA-ADRO-F-SHE-24D',
                'revisi' => Setting::getValue('berita_acara_revisi', '0') ?: '0',
                'tglEfektif' => Setting::getValue('berita_acara_tgl_efektif', '2022-04-07') ?: '2022-04-07',
            ],
        ]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $validated = $request->validate([
            'siteCode' => ['required', 'string', 'max:50'],
            'lokasi' => ['nullable', 'string', 'max:100'],
            'noDokumen' => ['required', 'string', 'max:100'],
            'revisi' => ['nullable', 'string', 'max:50'],
            'tglEfektif' => ['nullable', 'date'],
        ]);

        $siteCode = strtoupper(trim($validated['siteCode']));
        $lokasi = trim($validated['lokasi'] ?? 'Wara');
        $noDokumen = trim($validated['noDokumen']);
        $revisi = trim($validated['revisi'] ?? '0');
        $tglEfektif = $validated['tglEfektif'] ?? '2022-04-07';

        Setting::setValue('berita_acara_site_code', $siteCode);
        Setting::setValue('berita_acara_lokasi', $lokasi);
        Setting::setValue('berita_acara_no_dokumen', $noDokumen);
        Setting::setValue('berita_acara_revisi', $revisi);
        Setting::setValue('berita_acara_tgl_efektif', $tglEfektif);

        // Update existing records' nomor surat to use the new locked site code
        BeritaAcara::updateSiteCode($siteCode);

        // Update document header fields on all existing records as well
        BeritaAcara::query()->update([
            'no_dokumen' => $noDokumen,
            'revisi' => $revisi,
            'tgl_efektif' => $tglEfektif,
        ]);

        return redirect()->back()->with('success', 'Pengaturan dokumen berita acara berhasil dikunci dan seluruh data telah diperbarui.');
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
