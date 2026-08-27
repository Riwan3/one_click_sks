<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSkPembinaanRequest;
use App\Http\Requests\UpdateSkPembinaanRequest;
use App\Http\Resources\SkPembinaanResource;
use App\Models\Employee;
use App\Models\MasterSpdk;
use App\Models\SkPembinaan;
use App\Services\SkPembinaanService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Inertia\Inertia;
use Inertia\Response;

class SkPembinaanController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private SkPembinaanService $skPembinaanService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from', now()->startOfMonth()->toDateString()));
        $dateTo = trim((string) $request->string('date_to', now()->toDateString()));
        $status = trim((string) $request->string('status'));
        $page = max(1, (int) $request->integer('page', 1));

        $skPembinaans = $this->skPembinaanService->paginate($search, $dateFrom ?: null, $dateTo ?: null, $status ?: null, $page, self::PER_PAGE);

        return Inertia::render('OnePower/SkPembinaan/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'status' => $status,
            'skPembinaans' => SkPembinaanResource::collection($skPembinaans->items())->resolve(),
            'pagination' => [
                'currentPage' => $skPembinaans->currentPage(),
                'lastPage' => $skPembinaans->lastPage(),
                'total' => $skPembinaans->total(),
            ],
            'canManage' => ! $request->user()?->isManagement(),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        return Inertia::render('OnePower/SkPembinaan/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
        ]);
    }

    public function store(StoreSkPembinaanRequest $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->skPembinaanService->create($request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/sk-pembinaan')->with('success', 'SK Pembinaan berhasil dibuat.');
    }

    public function edit(Request $request, SkPembinaan $skPembinaan): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        $skPembinaan->load(['employee', 'pengawas']);

        return Inertia::render('OnePower/SkPembinaan/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetSkPembinaan' => new SkPembinaanResource($skPembinaan),
        ]);
    }

    public function update(UpdateSkPembinaanRequest $request, SkPembinaan $skPembinaan): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->skPembinaanService->update($skPembinaan, $request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/sk-pembinaan')->with('success', 'SK Pembinaan berhasil diperbarui.');
    }

    public function destroy(Request $request, SkPembinaan $skPembinaan): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->skPembinaanService->delete($skPembinaan);

        return redirect()->back()->with('success', 'SK Pembinaan berhasil dihapus.');
    }

    public function import(Request $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $count = $this->skPembinaanService->importFromExcel($request->file('file'), $request->user()?->id);

        return redirect('/one-power/pembinaan/sk-pembinaan')->with('success', "Berhasil mengimport {$count} SK Pembinaan.");
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = ['nrp', 'perusahaan', 'tanggal_pelanggaran', 'lokasi', 'spdk_ref', 'jenis_pelanggaran', 'lubang', 'berlaku_sampai', 'dikeluarkan_di', 'dikeluarkan_tanggal', 'keterangan'];

        return ResponseFacade::streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, ['10001', 'PT. PUTRA PERKASA ABADI', '2026-08-01', 'CSA Inggris', 'SPDK/001/2026', 'Pelanggaran keselamatan kerja', '1', '2027-08-01', 'CSA Inggris', '2026-08-01', 'Contoh keterangan']);
            fclose($handle);
        }, 'template_sk_pembinaan.csv', ['Content-Type' => 'text/csv']);
    }

    public function nextNumber(Request $request): JsonResponse
    {
        $tanggal = trim((string) $request->string('dikeluarkan_tanggal'));
        $perusahaan = trim((string) $request->string('perusahaan'));

        if ($tanggal === '') {
            return response()->json(['nomorSurat' => null]);
        }

        return response()->json(['nomorSurat' => SkPembinaan::nextNumber($tanggal, $perusahaan ?: null)]);
    }

    public function show(SkPembinaan $skPembinaan): Response
    {
        $skPembinaan->load(['employee', 'pengawas']);

        return Inertia::render('OnePower/SkPembinaan/Print', [
            'skPembinaan' => new SkPembinaanResource($skPembinaan),
        ]);
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

    public function searchSpdk(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->string('keyword'));

        if (mb_strlen($keyword) < 2) {
            return response()->json([]);
        }

        return response()->json(MasterSpdk::query()->search($keyword)->orderBy('jenis_pelanggaran')->limit(20)->get());
    }
}
