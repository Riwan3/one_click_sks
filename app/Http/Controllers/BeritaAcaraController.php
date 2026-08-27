<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBeritaAcaraRequest;
use App\Http\Requests\UpdateBeritaAcaraRequest;
use App\Http\Resources\BeritaAcaraResource;
use App\Models\BeritaAcara;
use App\Models\Employee;
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
        ]);
    }

    public function create(Request $request): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        return Inertia::render('OnePower/BeritaAcara/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
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
            'targetItem' => new BeritaAcaraResource($beritaAcara),
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
            'item' => new BeritaAcaraResource($beritaAcara),
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

    private function notifCounts(): array
    {
        return ['signature' => 2, 'notification' => 3, 'message' => 1];
    }
}
