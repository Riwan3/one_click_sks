<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDetail;
use App\Services\AttendanceService;
use App\Support\MenuAccess;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private AttendanceService $attendanceService) {}

    public function index(Request $request): Response
    {
        $user = $request->user()?->loadMissing('employee');
        $roleStr = strtolower((string) ($user?->role ?? ''));
        $jabatanStr = strtoupper((string) ($user?->employee?->jabatan_ss6 ?? $user?->jabatan ?? ''));

        $isOperator = (bool) ($user && (
            $roleStr === 'operator'
            || str_contains($jabatanStr, 'OPERATOR')
            || str_contains(strtoupper($roleStr), 'OPERATOR')
        ));

        $isGroupLeader = (bool) ($user && (
            in_array($roleStr, ['group-leader', 'groupleader', 'gl'], true)
            || str_contains($roleStr, 'group-leader')
            || str_contains($roleStr, 'group leader')
            || str_contains($jabatanStr, 'GROUP LEADER')
            || str_contains($jabatanStr, 'GL ')
            || str_ends_with($jabatanStr, ' GL')
            || $jabatanStr === 'GL'
        ));

        $isPersonalOnly = $isOperator || $isGroupLeader;
        $userNrp = (string) ($user?->employee?->nrp ?: $user?->username);

        $search = trim((string) $request->string('search'));
        $exactNrp = null;
        if ($isPersonalOnly) {
            $exactNrp = $userNrp;
            $search = '';
        }

        $dateFrom = trim((string) $request->string('date_from')) ?: now()->startOfMonth()->format('Y-m-d');
        $dateTo = trim((string) $request->string('date_to')) ?: now()->format('Y-m-d');
        $category = trim((string) $request->string('category'));
        $page = max(1, (int) $request->integer('page', 1));

        $attendanceData = $this->attendanceService->paginate($search, $dateFrom, $dateTo, $category, $page, self::PER_PAGE, $exactNrp);
        $summary = $this->attendanceService->summary($search, $dateFrom, $dateTo, $exactNrp);

        $latestRecord = AttendanceDetail::latest('updated_at')->first();
        $lastSync = $latestRecord && $latestRecord->updated_at
            ? Carbon::parse($latestRecord->updated_at)->locale('id')->isoFormat('D MMMM Y HH:mm')
            : now()->locale('id')->isoFormat('D MMMM Y HH:mm');

        $items = collect($attendanceData->items())->map(function ($row) {
            $tanggalStr = '-';
            if ($row->tanggal) {
                try {
                    $tanggalStr = Carbon::parse($row->tanggal)->locale('id')->isoFormat('D MMMM Y');
                } catch (\Throwable) {
                    $tanggalStr = (string) $row->tanggal;
                }
            }

            return [
                'id' => $row->id,
                'nrp' => (string) ($row->nrp ?? '-'),
                'name' => (string) ($row->name ?? '-'),
                'position' => (string) ($row->position ?? '-'),
                'tanggal' => $tanggalStr,
                'rawTanggal' => $row->tanggal instanceof \DateTimeInterface ? $row->tanggal->format('Y-m-d') : (string) $row->tanggal,
                'jamIn' => $row->jam_in ?: '-',
                'jamOut' => $row->jam_out ?: '-',
                'job' => $row->job ?: '-',
                'dept' => $row->dept ?: '-',
                'company' => $row->company ?: '-',
                'code' => $row->code ?: '-',
                'siaCategory' => $row->sia_category,
                'device' => $row->device ?: '-',
                'owner' => $row->owner ?: '-',
                'deviceCount' => $row->device_count ?? 0,
                'deviceId' => $row->device_id ?: '-',
            ];
        })->values()->all();

        return Inertia::render('OnePower/Attendance/Index', [
            'user' => $this->currentUser(),
            'isPersonalOnly' => $isPersonalOnly,
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'category' => $category,
            'summary' => $summary,
            'items' => $items,
            'lastSync' => $lastSync,
            'pagination' => [
                'currentPage' => $attendanceData->currentPage(),
                'lastPage' => $attendanceData->lastPage(),
                'total' => $attendanceData->total(),
                'from' => $attendanceData->firstItem() ?? 0,
                'to' => $attendanceData->lastItem() ?? 0,
            ],
        ]);
    }

    public function importFromApi(Request $request): RedirectResponse
    {
        $user = $request->user()?->loadMissing('employee');
        $roleStr = strtolower((string) ($user?->role ?? ''));
        $jabatanStr = strtoupper((string) ($user?->employee?->jabatan_ss6 ?? $user?->jabatan ?? ''));
        $isRestricted = in_array($roleStr, ['group-leader', 'groupleader', 'gl', 'operator'], true)
            || str_contains($roleStr, 'group-leader')
            || str_contains($jabatanStr, 'GROUP LEADER')
            || str_contains($jabatanStr, 'OPERATOR');

        if ($isRestricted) {
            return redirect('/one-power/personal-data/attendance')->with('error', 'Anda tidak memiliki akses untuk import data.');
        }

        $bulan = (int) ($request->integer('bulan') ?: now()->month);
        $tahun = (int) ($request->integer('tahun') ?: now()->year);
        $dept = (string) ($request->string('dept') ?: 'PRO');

        $result = $this->attendanceService->importFromApi($bulan, $tahun, $dept);

        return redirect('/one-power/personal-data/attendance')->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}
