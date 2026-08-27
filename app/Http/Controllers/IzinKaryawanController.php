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
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IzinKaryawanController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private IzinKaryawanService $izinKaryawanService) {}

    public function index(Request $request): Response
    {
        $isAdmin = (bool) $request->user()?->isAdmin();
        $isMedic = (bool) $request->user()?->isMedic();

        $filters = [
            // Medic hanya melihat/mengelola data jenis "sakit" (setara V1's
            // IzinKaryawanController::index()) — dipaksa terlepas dari query string.
            'jenis_izin' => $isMedic ? 'sakit' : trim((string) $request->string('jenis_izin')),
            'search' => trim((string) $request->string('search')),
            'date_from' => trim((string) $request->string('date_from')),
            'date_to' => trim((string) $request->string('date_to')),
            'status' => trim((string) $request->string('status')),
            'created_by' => $isAdmin ? null : $request->user()?->id,
        ];
        $page = max(1, (int) $request->integer('page', 1));

        $izins = $this->izinKaryawanService->paginate($filters, $page, self::PER_PAGE);

        return Inertia::render('OnePower/IzinKaryawan/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'isAdmin' => $isAdmin,
            'isMedic' => $isMedic,
            'filters' => $filters,
            'izins' => IzinKaryawanResource::collection($izins->items())->resolve(),
            'pagination' => [
                'currentPage' => $izins->currentPage(),
                'lastPage' => $izins->lastPage(),
                'total' => $izins->total(),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('OnePower/IzinKaryawan/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'isMedic' => (bool) $request->user()?->isMedic(),
        ]);
    }

    public function store(StoreIzinKaryawanRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('file_bukti');

        // Medic hanya boleh input data jenis "sakit" (setara AuthMiddleware::isMedic()
        // di V1's IzinKaryawanController::add()) — dipaksa di server terlepas dari
        // apa yang dikirim form, bukan cuma disembunyikan di UI.
        if ($request->user()?->isMedic()) {
            $data['jenis_izin'] = 'sakit';
        }

        $this->izinKaryawanService->create($data, $request->file('file_bukti'), $request->user()?->id);

        return redirect('/one-power/deviasi-operator/izin-karyawan')->with('success', 'Pengajuan izin berhasil ditambahkan.');
    }

    public function edit(Request $request, IzinKaryawan $izin): Response
    {
        // Medic hanya boleh mengakses data jenis "sakit" (setara V1's
        // IzinKaryawanController::edit()).
        abort_if($request->user()?->isMedic() && $izin->jenis_izin !== IzinJenis::Sakit, 403);

        return Inertia::render('OnePower/IzinKaryawan/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'isMedic' => (bool) $request->user()?->isMedic(),
            'targetIzin' => new IzinKaryawanResource($izin),
        ]);
    }

    public function update(UpdateIzinKaryawanRequest $request, IzinKaryawan $izin): RedirectResponse
    {
        abort_if($request->user()?->isMedic() && $izin->jenis_izin !== IzinJenis::Sakit, 403);

        $data = $request->safe()->except('file_bukti');

        if ($request->user()?->isMedic()) {
            $data['jenis_izin'] = 'sakit';
        }

        $this->izinKaryawanService->update($izin, $data, $request->file('file_bukti'));

        return redirect('/one-power/deviasi-operator/izin-karyawan')->with('success', 'Data izin berhasil diperbarui.');
    }

    public function destroy(Request $request, IzinKaryawan $izin): RedirectResponse
    {
        abort_if($request->user()?->isMedic() && $izin->jenis_izin !== IzinJenis::Sakit, 403);

        $this->izinKaryawanService->delete($izin);

        return redirect()->back()->with('success', 'Data izin berhasil dihapus.');
    }

    public function approve(Request $request, IzinKaryawan $izin): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $this->izinKaryawanService->approve($izin, $request->user()?->id);

        return redirect()->back()->with('success', 'Pengajuan izin disetujui.');
    }

    public function decline(Request $request, IzinKaryawan $izin): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $this->izinKaryawanService->decline($izin, $request->user()?->id);

        return redirect()->back()->with('success', 'Pengajuan izin ditolak.');
    }

    public function reset(Request $request, IzinKaryawan $izin): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $this->izinKaryawanService->reset($izin);

        return redirect()->back()->with('success', 'Status pengajuan izin direset.');
    }

    public function cancel(Request $request, IzinKaryawan $izin): RedirectResponse
    {
        $isOwner = $izin->created_by === $request->user()?->id;
        abort_unless($isOwner || $request->user()?->isAdmin(), 403);

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

    /**
     * Admin-only CSV export, matching V1's IzinKaryawanController::export()
     * column format: No Surat, Jenis, Nama, NRP, Jabatan, Departemen, Status,
     * Tanggal, File — with the File column as an Excel HYPERLINK() formula
     * pointing at the stored bukti file (V1 built this from base_url() +
     * assets/uploads/izin_karyawan/; here it's the public-disk URL instead).
     */
    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $filters = [
            'jenis_izin' => trim((string) $request->string('jenis_izin')),
            'search' => trim((string) $request->string('search')),
            'date_from' => trim((string) $request->string('date_from')),
            'date_to' => trim((string) $request->string('date_to')),
            'status' => trim((string) $request->string('status')),
            'created_by' => null,
        ];

        $rows = $this->izinKaryawanService->allForExport($filters);

        $filename = 'izin_karyawan_'.now()->format('Y-m-d_H-i-s').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['No Surat', 'Jenis', 'Nama', 'NRP', 'Jabatan', 'Departemen', 'Status', 'Tanggal', 'File']);

            foreach ($rows as $row) {
                fwrite($output, $this->exportCsvLine($row)."\r\n");
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    private function exportCsvLine(IzinKaryawan $row): string
    {
        $fileCell = '-';

        if ($row->file_bukti) {
            $url = Storage::disk('public')->url($row->file_bukti);
            $filename = basename($row->file_bukti);
            $fileCell = '=HYPERLINK("'.$url.'","'.$filename.'")';
        }

        $fields = [
            $row->no_surat ?? '-',
            $row->jenis_izin->label(),
            $row->nama ?? '-',
            $row->nrp ?? '-',
            $row->jabatan ?? '-',
            $row->departemen ?? '-',
            $row->status->label(),
            $row->created_at ? $row->created_at->format('d/m/Y H:i') : '-',
            $fileCell,
        ];

        $line = [];
        foreach ($fields as $field) {
            $line[] = str_starts_with((string) $field, '=HYPERLINK')
                ? $field
                : '"'.str_replace('"', '""', (string) $field).'"';
        }

        return implode(',', $line);
    }
}
