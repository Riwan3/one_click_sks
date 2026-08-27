<?php

namespace App\Http\Controllers;

use App\Enums\IzinJenis;
use App\Http\Requests\StoreIzinKaryawanRequest;
use App\Http\Requests\UpdateIzinKaryawanRequest;
use App\Http\Resources\IzinKaryawanResource;
use App\Models\IzinKaryawan;
use App\Services\IzinKaryawanService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IzinKaryawanController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private IzinKaryawanService $izinKaryawanService) {}

    public function index(Request $request): Response
    {
        $filters = [
            'jenis_izin' => trim((string) $request->string('jenis_izin')),
            'search' => trim((string) $request->string('search')),
            'date_from' => trim((string) $request->string('date_from')),
            'date_to' => trim((string) $request->string('date_to')),
            'status' => trim((string) $request->string('status')),
        ];
        $page = max(1, (int) $request->integer('page', 1));

        $izins = $this->izinKaryawanService->paginate($filters, $page, self::PER_PAGE);

        return Inertia::render('OnePower/IzinKaryawan/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'filters' => $filters,
            'izins' => IzinKaryawanResource::collection($izins->items())->resolve(),
            'pagination' => [
                'currentPage' => $izins->currentPage(),
                'lastPage' => $izins->lastPage(),
                'total' => $izins->total(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('OnePower/IzinKaryawan/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
        ]);
    }

    public function store(StoreIzinKaryawanRequest $request): RedirectResponse
    {
        $this->izinKaryawanService->create($request->safe()->except('file_bukti'), $request->file('file_bukti'), $request->user()?->id);

        return redirect('/one-power/deviasi-operator/izin-karyawan')->with('success', 'Pengajuan izin berhasil ditambahkan.');
    }

    public function edit(IzinKaryawan $izin): Response
    {
        return Inertia::render('OnePower/IzinKaryawan/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetIzin' => new IzinKaryawanResource($izin),
        ]);
    }

    public function update(UpdateIzinKaryawanRequest $request, IzinKaryawan $izin): RedirectResponse
    {
        $this->izinKaryawanService->update($izin, $request->safe()->except('file_bukti'), $request->file('file_bukti'));

        return redirect('/one-power/deviasi-operator/izin-karyawan')->with('success', 'Data izin berhasil diperbarui.');
    }

    public function destroy(IzinKaryawan $izin): RedirectResponse
    {
        $this->izinKaryawanService->delete($izin);

        return redirect()->back()->with('success', 'Data izin berhasil dihapus.');
    }

    public function approve(Request $request, IzinKaryawan $izin): RedirectResponse
    {
        $this->izinKaryawanService->approve($izin, $request->user()?->id);

        return redirect()->back()->with('success', 'Pengajuan izin disetujui.');
    }

    public function decline(Request $request, IzinKaryawan $izin): RedirectResponse
    {
        $this->izinKaryawanService->decline($izin, $request->user()?->id);

        return redirect()->back()->with('success', 'Pengajuan izin ditolak.');
    }

    public function reset(IzinKaryawan $izin): RedirectResponse
    {
        $this->izinKaryawanService->reset($izin);

        return redirect()->back()->with('success', 'Status pengajuan izin direset.');
    }

    public function cancel(IzinKaryawan $izin): RedirectResponse
    {
        $this->izinKaryawanService->cancel($izin);

        return redirect()->back()->with('success', 'Pengajuan izin dibatalkan.');
    }

    public function searchEmployee(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->string('keyword'));

        if ($keyword === '') {
            return response()->json([]);
        }

        return response()->json($this->izinKaryawanService->searchEmployee($keyword));
    }

    public function getNextSuratNumber(Request $request): JsonResponse
    {
        $jenis = IzinJenis::tryFrom((string) $request->string('jenis_izin')) ?? IzinJenis::Izin;

        return response()->json(['no_surat' => IzinKaryawan::nextSuratNumber($jenis)]);
    }

    private function notifCounts(): array
    {
        return ['signature' => 2, 'notification' => 3, 'message' => 1];
    }
}
