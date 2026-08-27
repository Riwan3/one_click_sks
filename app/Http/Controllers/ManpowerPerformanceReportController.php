<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\ManpowerPerformanceReportService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ManpowerPerformanceReportController extends Controller
{
    // V1 (database/mpr -> DashboardController::mprIndex) defaults the period filter to
    // Jan-current_year..current_month, capped at 24 months. We default the drilldown window
    // to a trailing 6 months per the feature brief, capped the same way V1 caps its range.
    private const DEFAULT_MONTHS_BACK = 6;

    private const MAX_MONTHS_BACK = 24;

    public function __construct(private ManpowerPerformanceReportService $reportService) {}

    public function index(Request $request): Response
    {
        $nrp = trim((string) $request->string('nrp'));
        $monthsBack = $this->resolveMonthsBack($request);

        $employee = $nrp === '' ? null : $this->findEmployee($nrp);
        $rows = $employee ? $this->reportService->monthlyBreakdown($employee, $monthsBack) : collect();

        return Inertia::render('OnePower/ManpowerPerformanceReport/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'nrp' => $nrp,
            'monthsBack' => $monthsBack,
            'employee' => $employee ? [
                'id' => $employee->id,
                'nrp' => $employee->nrp,
                'name' => $employee->name,
                'jabatan' => $employee->jabatan_ss6,
                'unit' => $employee->unit,
            ] : null,
            'notFound' => $nrp !== '' && ! $employee,
            'rows' => $rows->values(),
            'summary' => $employee ? $this->reportService->summarize($rows) : null,
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

    private function findEmployee(string $nrp): ?Employee
    {
        return Employee::query()
            ->where('nrp', $nrp)
            ->orWhere('name', 'like', "%{$nrp}%")
            ->first();
    }

    private function resolveMonthsBack(Request $request): int
    {
        $monthsBack = (int) $request->integer('months_back', self::DEFAULT_MONTHS_BACK);

        return min(max($monthsBack, 1), self::MAX_MONTHS_BACK);
    }
}
