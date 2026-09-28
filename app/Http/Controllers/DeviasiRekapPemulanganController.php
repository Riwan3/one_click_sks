<?php

namespace App\Http\Controllers;

use App\Http\Resources\DeviasiRekapPemulanganResource;
use App\Imports\DeviasiRekapPemulanganImport;
use App\Models\DeviasiRekapPemulangan;
use App\Models\Employee;
use App\Services\DeviasiRekapPemulanganService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeviasiRekapPemulanganController extends Controller
{
    private const PER_PAGE = 15;

    public function __construct(private readonly DeviasiRekapPemulanganService $rekapService) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->canAccessRekapPemulangan(), 403);

        $filters = [
            'search' => trim((string) $request->string('search')),
            'date_from' => $request->has('date_from') ? trim((string) $request->string('date_from')) : date('Y-m-01'),
            'date_to' => $request->has('date_to') ? trim((string) $request->string('date_to')) : date('Y-m-d'),
            'shift' => trim((string) $request->string('shift')),
        ];

        $page = max(1, (int) $request->integer('page', 1));
        $paginator = $this->rekapService->paginate($filters, $page, self::PER_PAGE);
        $stats = $this->rekapService->getStats();

        return Inertia::render('OnePower/DeviasiOperator/RekapPemulangan/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'filters' => $filters,
            'stats' => $stats,
            'data' => DeviasiRekapPemulanganResource::collection($paginator->items())->resolve(),
            'pagination' => [
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'perPage' => $paginator->perPage(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canAccessRekapPemulangan(), 403);

        $validated = $request->validate([
            'nrp' => ['required', 'string', 'max:50'],
            'nama' => ['nullable', 'string', 'max:150'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'shift' => ['required', 'in:D,N'],
            'jam' => ['required', 'string'],
            'alasan' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
            'konfirmasi' => ['nullable', 'string', 'max:150'],
        ]);

        $user = $request->user();
        $userDisplay = $user?->name ?? $user?->username ?? 'User';

        $this->rekapService->create($validated, $userDisplay);

        return redirect()->back()->with('success', 'Data pemulangan berhasil ditambahkan.');
    }

    public function update(Request $request, DeviasiRekapPemulangan $rekapPemulangan): RedirectResponse
    {
        abort_unless($request->user()?->canAccessRekapPemulangan(), 403);

        $validated = $request->validate([
            'nrp' => ['required', 'string', 'max:50'],
            'nama' => ['nullable', 'string', 'max:150'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'shift' => ['required', 'in:D,N'],
            'jam' => ['required', 'string'],
            'alasan' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
            'konfirmasi' => ['nullable', 'string', 'max:150'],
        ]);

        $user = $request->user();
        $userDisplay = $user?->name ?? $user?->username ?? 'User';

        $this->rekapService->update($rekapPemulangan, $validated, $userDisplay);

        return redirect()->back()->with('success', 'Data pemulangan berhasil diperbarui.');
    }

    public function destroy(Request $request, DeviasiRekapPemulangan $rekapPemulangan): RedirectResponse
    {
        abort_unless($request->user()?->canAccessRekapPemulangan(), 403);

        $this->rekapService->delete($rekapPemulangan);

        return redirect()->back()->with('success', 'Data pemulangan berhasil dihapus.');
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canAccessRekapPemulangan(), 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:20480'],
        ]);

        $user = $request->user();
        $userDisplay = $user?->name ?? $user?->username ?? 'Import';

        $import = new DeviasiRekapPemulanganImport($userDisplay);
        Excel::import($import, $request->file('file'));

        $count = $import->getImportedCount();

        return redirect()->back()->with('success', "Berhasil mengimpor {$count} data pemulangan.");
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = ['no', 'nrp', 'nama', 'jabatan', 'tanggal', 'shift', 'jam pulang', 'keterangan', 'konfirmasi'];

        return ResponseFacade::streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, ['1', '21001234', 'Ahmad Santoso', 'Operator Hauler', date('Y-m-d'), 'D', '10:30', 'Sakit kepala / pusing', 'Disetujui GL Pit']);
            fputcsv($handle, ['2', '21005678', 'Budi Pratama', 'Operator Dozer', date('Y-m-d'), 'N', '23:15', 'Izin urusan keluarga', 'Disetujui Foreman']);
            fclose($handle);
        }, 'template_rekap_pemulangan.csv', ['Content-Type' => 'text/csv']);
    }

    public function searchEmployee(Request $request): JsonResponse
    {
        abort_unless($request->user()?->canAccessRekapPemulangan(), 403);

        $keyword = trim((string) $request->string('keyword'));

        if ($keyword === '') {
            return response()->json([]);
        }

        $employees = Employee::query()
            ->search($keyword)
            ->limit(10)
            ->get(['nrp', 'name', 'jabatan_ss6', 'unit']);

        return response()->json($employees);
    }
}
