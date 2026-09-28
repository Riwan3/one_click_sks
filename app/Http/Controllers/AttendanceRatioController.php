<?php

namespace App\Http\Controllers;

use App\Services\AttendanceRatioService;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceRatioController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private AttendanceRatioService $atrService) {}

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
        $bulan = (int) ($request->integer('bulan') ?: now()->month);
        $tahun = (int) ($request->integer('tahun') ?: now()->year);
        $kategori = trim((string) $request->string('kategori'));
        $filterLowAtr = $request->boolean('filter_low_atr');
        $page = max(1, (int) $request->integer('page', 1));

        $filters = ['bulan' => $bulan, 'tahun' => $tahun, 'kategori' => $kategori];
        if ($isPersonalOnly) {
            $filters['nrp_operator'] = $userNrp;
            $search = '';
        } else {
            $filters['search'] = $search;
        }

        $threshold = $this->atrService->threshold();
        $lowAtrRows = [];
        $quartalInfo = null;

        if ($filterLowAtr) {
            $quartal = (int) ceil($bulan / 3);
            $quartalInfo = ['quartal' => $quartal, 'tahun' => $tahun, 'label' => $this->atrService->quartalLabel($quartal)];

            $restrictGlId = $isGroupLeader ? $user?->employee?->id : null;
            $restrictNrp = $isOperator ? $userNrp : null;
            $lowAtrRows = $this->atrService->quarterlyLowAtr($threshold, $tahun, $quartal, $restrictGlId, $restrictNrp, $search ?: null);
        }

        $rows = $filterLowAtr ? [] : $this->atrService->monthlySummary($filters, $page, self::PER_PAGE);
        $total = $filterLowAtr ? count($lowAtrRows) : $this->atrService->countMonthlySummary($filters);
        $stats = $this->atrService->monthlyDashboardStats($filters);

        $items = $filterLowAtr
            ? array_map(fn ($r) => [
                'nrp' => (string) $r->nrp,
                'name' => (string) ($r->name ?: '-'),
                'jabatan' => (string) ($r->jabatan_ss6 ?: '-'),
                'glName' => $r->gl_name ?: '-',
                'employeePhone' => $r->employee_phone,
                'hadir' => (int) $r->hadir,
                'sakit' => (int) $r->sakit,
                'izin' => (int) $r->izin,
                'alpa' => (int) $r->alpa,
                'training' => (int) $r->training,
                'atr' => (float) $r->atr,
            ], $lowAtrRows)
            : array_map(fn ($r) => [
                'nrp' => (string) $r->nrp,
                'name' => (string) ($r->name ?: '-'),
                'dept' => (string) ($r->dept ?: '-'),
                'jabatan' => (string) ($r->jabatan ?: '-'),
                'glName' => $r->gl_name ?: '-',
                'employeePhone' => $r->employee_phone,
                'd' => (int) $r->d,
                'n' => (int) $r->n,
                's' => (int) $r->s,
                'i' => (int) $r->i,
                'a' => (int) $r->a,
                'atr' => (float) $r->atr,
            ], $rows);

        return Inertia::render('OnePower/AttendanceRatio/Index', [
            'user' => $this->currentUser(),
            'isPersonalOnly' => $isPersonalOnly,
            'isAdminOrPimpinan' => ! $isPersonalOnly,
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kategori' => $kategori,
            'filterLowAtr' => $filterLowAtr,
            'threshold' => $threshold,
            'quartalInfo' => $quartalInfo,
            'stats' => $stats,
            'items' => $items,
            'pagination' => $filterLowAtr ? null : [
                'currentPage' => $page,
                'lastPage' => max(1, (int) ceil($total / self::PER_PAGE)),
                'total' => $total,
                'from' => $total === 0 ? 0 : (($page - 1) * self::PER_PAGE) + 1,
                'to' => min($total, $page * self::PER_PAGE),
            ],
        ]);
    }

    public function saveThreshold(Request $request): RedirectResponse
    {
        $user = $request->user();
        $roleStr = strtolower((string) ($user?->role ?? ''));
        if (! in_array($roleStr, ['admin', 'pimpinan'], true)) {
            return redirect('/one-power/personal-data/attendance-ratio')->with('error', 'Anda tidak memiliki akses untuk mengubah threshold ATR.');
        }

        $request->validate(['atr_threshold' => ['required', 'numeric', 'min:0', 'max:100']]);
        $this->atrService->saveThreshold((float) $request->input('atr_threshold'));

        return redirect('/one-power/personal-data/attendance-ratio')->with('success', 'Threshold ATR berhasil disimpan.');
    }
}
