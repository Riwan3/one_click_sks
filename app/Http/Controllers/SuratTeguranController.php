<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuratTeguranRequest;
use App\Http\Requests\UpdateSuratTeguranRequest;
use App\Http\Resources\SuratTeguranResource;
use App\Models\Employee;
use App\Models\SuratTeguran;
use App\Services\SuratTeguranService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Inertia\Inertia;
use Inertia\Response;

class SuratTeguranController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private SuratTeguranService $suratTeguranService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));
        $page = max(1, (int) $request->integer('page', 1));

        $suratTegurans = $this->suratTeguranService->paginate($search, $dateFrom ?: null, $dateTo ?: null, $page, self::PER_PAGE);

        return Inertia::render('OnePower/SuratTeguran/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'suratTegurans' => SuratTeguranResource::collection($suratTegurans->items())->resolve(),
            'pagination' => [
                'currentPage' => $suratTegurans->currentPage(),
                'lastPage' => $suratTegurans->lastPage(),
                'total' => $suratTegurans->total(),
            ],
            'canManage' => ! $request->user()?->isManagement(),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        return Inertia::render('OnePower/SuratTeguran/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
        ]);
    }

    public function store(StoreSuratTeguranRequest $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->suratTeguranService->create($request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/surat-teguran')->with('success', 'Surat teguran berhasil dibuat.');
    }

    public function edit(Request $request, SuratTeguran $suratTeguran): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        return Inertia::render('OnePower/SuratTeguran/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetSuratTeguran' => new SuratTeguranResource($suratTeguran),
        ]);
    }

    public function update(UpdateSuratTeguranRequest $request, SuratTeguran $suratTeguran): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->suratTeguranService->update($suratTeguran, $request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/surat-teguran')->with('success', 'Surat teguran berhasil diperbarui.');
    }

    public function destroy(Request $request, SuratTeguran $suratTeguran): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->suratTeguranService->delete($suratTeguran);

        return redirect()->back()->with('success', 'Surat teguran berhasil dihapus.');
    }

    public function import(Request $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $count = $this->suratTeguranService->importFromExcel($request->file('file'), $request->user()?->id);

        return redirect('/one-power/pembinaan/surat-teguran')->with('success', "Berhasil mengimport {$count} surat teguran.");
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = ['nrp', 'tanggal_kejadian', 'tempat_kejadian', 'jenis_absen', 'jenis_perilaku', 'jenis_ceroboh', 'jenis_hasil_kerja', 'jenis_menolak_perintah', 'jenis_pelanggaran_peraturan', 'penjelasan_singkat', 'dikeluarkan_di', 'dikeluarkan_tanggal'];

        return ResponseFacade::streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, ['10001', '2026-08-01', 'CSA Inggris', 'ya', 'tidak', 'tidak', 'tidak', 'tidak', 'tidak', 'Contoh penjelasan singkat', 'CSA Inggris', '2026-08-01']);
            fclose($handle);
        }, 'template_surat_teguran.csv', ['Content-Type' => 'text/csv']);
    }

    public function show(SuratTeguran $suratTeguran): Response
    {
        return Inertia::render('OnePower/SuratTeguran/Print', [
            'suratTeguran' => new SuratTeguranResource($suratTeguran),
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
}
