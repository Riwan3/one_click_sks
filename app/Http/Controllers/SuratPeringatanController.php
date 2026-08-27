<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuratPeringatanRequest;
use App\Http\Requests\UpdateSuratPeringatanRequest;
use App\Http\Resources\SuratPeringatanResource;
use App\Models\Employee;
use App\Models\SuratPeringatan;
use App\Services\SuratPeringatanService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Inertia\Inertia;
use Inertia\Response;

class SuratPeringatanController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private SuratPeringatanService $suratPeringatanService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from', now()->startOfMonth()->toDateString()));
        $dateTo = trim((string) $request->string('date_to', now()->toDateString()));
        $page = max(1, (int) $request->integer('page', 1));

        $suratPeringatans = $this->suratPeringatanService->paginate($search, $dateFrom ?: null, $dateTo ?: null, $page, self::PER_PAGE);

        return Inertia::render('OnePower/SuratPeringatan/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'suratPeringatans' => SuratPeringatanResource::collection($suratPeringatans->items())->resolve(),
            'pagination' => [
                'currentPage' => $suratPeringatans->currentPage(),
                'lastPage' => $suratPeringatans->lastPage(),
                'total' => $suratPeringatans->total(),
            ],
            'canManage' => ! $request->user()?->isManagement(),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        return Inertia::render('OnePower/SuratPeringatan/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
        ]);
    }

    public function store(StoreSuratPeringatanRequest $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->suratPeringatanService->create($request->validated(), $request->user()?->id, $request->user()?->id);

        return redirect('/one-power/pembinaan/surat-peringatan')->with('success', 'Surat peringatan berhasil dibuat.');
    }

    public function edit(Request $request, SuratPeringatan $suratPeringatan): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        $suratPeringatan->load(['employee', 'pengawas']);

        return Inertia::render('OnePower/SuratPeringatan/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetSuratPeringatan' => new SuratPeringatanResource($suratPeringatan),
        ]);
    }

    public function update(UpdateSuratPeringatanRequest $request, SuratPeringatan $suratPeringatan): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->suratPeringatanService->update($suratPeringatan, $request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/surat-peringatan')->with('success', 'Surat peringatan berhasil diperbarui.');
    }

    public function destroy(Request $request, SuratPeringatan $suratPeringatan): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->suratPeringatanService->delete($suratPeringatan);

        return redirect()->back()->with('success', 'Surat peringatan berhasil dihapus.');
    }

    public function import(Request $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $count = $this->suratPeringatanService->importFromExcel($request->file('file'), $request->user()?->id);

        return redirect('/one-power/pembinaan/surat-peringatan')->with('success', "Berhasil mengimport {$count} surat peringatan.");
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = ['nrp', 'jenis_pembinaan', 'kode_pelanggaran', 'tanggal_kejadian', 'tempat_kejadian', 'pelanggaran_absen_terlambat', 'pelanggaran_perilaku', 'pelanggaran_ceroboh_lalai', 'pelanggaran_hasil_kerja', 'pelanggaran_menolak_perintah', 'pelanggaran_peraturan', 'detail_pelanggaran', 'sanksi_masa_berlaku', 'dikeluarkan_di', 'dikeluarkan_tanggal'];

        return ResponseFacade::streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, ['10001', 'Peringatan Pertama', 'A.1.1', '2026-08-01', 'CSA Inggris', 'ya', 'tidak', 'tidak', 'tidak', 'tidak', 'tidak', 'Contoh detail pelanggaran', '6', 'CSA Inggris', '2026-08-01']);
            fclose($handle);
        }, 'template_surat_peringatan.csv', ['Content-Type' => 'text/csv']);
    }

    public function show(SuratPeringatan $suratPeringatan): Response
    {
        $suratPeringatan->load(['employee', 'pengawas']);

        return Inertia::render('OnePower/SuratPeringatan/Print', [
            'suratPeringatan' => new SuratPeringatanResource($suratPeringatan),
        ]);
    }

    public function checkActive(Request $request): JsonResponse
    {
        $employeeId = (int) $request->integer('employee_id');
        $tanggal = $request->string('tanggal')->toString() ?: null;

        if (! $employeeId) {
            return response()->json(null);
        }

        $active = $this->suratPeringatanService->activeSpFor($employeeId, $tanggal);

        return response()->json($active ? new SuratPeringatanResource($active->load('employee')) : null);
    }

    public function nextNumber(Request $request): JsonResponse
    {
        $jenis = $request->string('jenis_pembinaan')->toString() ?: 'Peringatan Pertama';
        $tanggal = $request->string('tanggal')->toString() ?: now()->toDateString();

        return response()->json(['nomor_sp' => SuratPeringatan::nextNumber($jenis, $tanggal)]);
    }

    public function searchEmployee(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->string('keyword'));

        if ($keyword === '') {
            return response()->json([]);
        }

        return response()->json(
            Employee::query()->search($keyword)->limit(10)->get(['id', 'nrp', 'name', 'jabatan_ss6', 'unit'])
        );
    }

    private function notifCounts(): array
    {
        return ['signature' => 2, 'notification' => 3, 'message' => 1];
    }
}
