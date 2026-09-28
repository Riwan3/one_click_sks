<?php

namespace App\Http\Controllers;

use App\Http\Resources\OdrAssignmentResource;
use App\Services\OdrComplianceReportService;
use App\Support\MenuAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Report Compliance One-Reporting (tab Laporan, Rekap Harian, Rekap Bulanan + export). */
class OdrComplianceReportController extends Controller
{
    public function __construct(private OdrComplianceReportService $reportService) {}

    public function index(Request $request): Response
    {
        Gate::authorize('access-compliance-report');

        $filters = $this->reportService->normalizeFilters($request->query());
        $tab = $filters['tab'];

        return Inertia::render('OneReporting/LaporanCompliance', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_reporting_menu'),
            'filters' => $filters,
            'reportRows' => $tab === 'laporan' ? OdrAssignmentResource::collection($this->reportService->reportRows($filters))->resolve() : [],
            'dailyRecap' => $tab === 'rekap_harian' ? $this->reportService->dailyRecap($filters) : [],
            'monthlyRecap' => $tab === 'rekap_bulanan' ? $this->reportService->monthlyRecap($filters['bulan'], $filters['tahun']) : null,
            'areaOptions' => $this->reportService->areaOptions(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('access-compliance-report');

        $lines = $this->reportService->exportRows($this->reportService->normalizeFilters($request->query()));

        return response()->streamDownload(function () use ($lines) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($lines as $line) fputcsv($out, $line);
            fclose($out);
        }, 'laporan_compliance_'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
    }
}
