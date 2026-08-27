<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceMonthlyRosterRequest;
use App\Http\Requests\UpdateAttendanceMonthlyRosterRequest;
use App\Http\Resources\AttendanceMonthlyRosterResource;
use App\Models\AttendanceMonthlyRoster;
use App\Models\Employee;
use App\Services\AttendanceMonthlyRosterService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves the "Attendance" menu item under One-Power > Personal Data Operator.
 *
 * Rebuilt to match ONECLICK V1's actual model for this menu context: a
 * monthly aggregate roster per employee (AttendanceMonthlyRoster), populated
 * mainly via Excel import, rather than a daily per-employee log. The
 * pre-existing daily-log `Attendance` model/table is untouched — it still
 * backs DashboardService::attendanceStats() for the OnePower Dashboard.
 */
class AttendanceController extends Controller
{
    private const PER_PAGE = 15;

    /** Default ATR MIN threshold used by the "ATR < threshold" filter toggle. */
    private const DEFAULT_ATR_THRESHOLD = 95;

    public function __construct(private AttendanceMonthlyRosterService $rosterService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $bulan = (int) ($request->integer('bulan') ?: now()->month);
        $tahun = (int) ($request->integer('tahun') ?: now()->year);
        $kategori = trim((string) $request->string('kategori')) ?: null;
        $atrThreshold = $request->filled('atr_threshold')
            ? (float) $request->input('atr_threshold')
            : self::DEFAULT_ATR_THRESHOLD;
        $filterLowAtr = $request->boolean('filter_low_atr');
        $page = max(1, (int) $request->integer('page', 1));

        $items = $this->rosterService->paginate($search ?: null, $bulan, $tahun, $page, self::PER_PAGE, $kategori, $filterLowAtr, $atrThreshold);

        return Inertia::render('OnePower/Attendance/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => ['signature' => 2, 'notification' => 3, 'message' => 1],
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kategori' => $kategori,
            'atrThreshold' => $atrThreshold,
            'filterLowAtr' => $filterLowAtr,
            'summary' => $this->rosterService->summaryStats($search ?: null, $bulan, $tahun, $kategori),
            'codeColumns' => AttendanceMonthlyRoster::CODE_COLUMNS,
            'items' => AttendanceMonthlyRosterResource::collection($items->items())->resolve(),
            'pagination' => [
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function store(StoreAttendanceMonthlyRosterRequest $request): RedirectResponse
    {
        $this->rosterService->create($request->validated(), $request->user()?->id);

        return redirect()->back()->with('success', 'Data roster absensi berhasil ditambahkan.');
    }

    public function update(UpdateAttendanceMonthlyRosterRequest $request, AttendanceMonthlyRoster $attendance): RedirectResponse
    {
        $this->rosterService->update($attendance, $request->validated());

        return redirect()->back()->with('success', 'Data roster absensi berhasil diperbarui.');
    }

    public function destroy(AttendanceMonthlyRoster $attendance): RedirectResponse
    {
        $this->rosterService->delete($attendance);

        return redirect()->back()->with('success', 'Data roster absensi berhasil dihapus.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'digits:4'],
        ]);

        $result = $this->rosterService->importFromExcel(
            $request->file('file'),
            (int) $request->integer('bulan'),
            (int) $request->integer('tahun'),
            $request->user()?->id
        );

        $message = "Berhasil mengimport {$result['imported']} data absensi.";

        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} baris dilewati (NRP kosong/tidak valid atau karyawan tidak ditemukan).";
        }

        return redirect()->back()->with('success', $message);
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = array_merge(
            ['nrp', 'name', 'dept', 'jabatan', 'posisi', 'comp', 'atr'],
            AttendanceMonthlyRoster::CODE_COLUMNS
        );

        return ResponseFacade::streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, array_merge(
                ['10001', 'NAMA CONTOH', 'PRODUKSI', 'OPERATOR HD785', 'OPERATOR', 'ORGANIK', '95.5'],
                array_fill(0, count(AttendanceMonthlyRoster::CODE_COLUMNS), 0)
            ));
            fclose($handle);
        }, 'template_attendance_monthly_roster.csv', ['Content-Type' => 'text/csv']);
    }

    public function searchEmployee(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->string('keyword'));

        if ($keyword === '') {
            return response()->json([]);
        }

        $employees = Employee::query()->search($keyword)->limit(10)->get(['id', 'nrp', 'name', 'unit']);

        return response()->json($employees);
    }
}
