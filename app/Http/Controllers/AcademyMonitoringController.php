<?php

namespace App\Http\Controllers;

use App\Http\Resources\AcademyMonitoringResource;
use App\Models\AcademyAssessment;
use App\Models\AcademyResult;
use App\Services\AcademyMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Aksi tab Monitoring di Evaluasi Kompetensi (detail, export, hapus peserta).
 * Data halaman dirender oleh EvaluasiKompetensiController@index.
 */
class AcademyMonitoringController extends Controller
{
    public function __construct(private AcademyMonitoringService $monitoringService) {}

    public function show(AcademyResult $result): JsonResponse
    {
        return response()->json($this->monitoringService->detail($result));
    }

    public function destroy(AcademyAssessment $assessment, string $nrp): RedirectResponse
    {
        Gate::authorize('manage-academy-evaluation');

        $this->monitoringService->removeAssignment($assessment->id, $nrp);

        return back()->with('success', 'Karyawan berhasil dihapus dari assessment ini.');
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $filters = $this->monitoringService->normalizeFilters($request->query());
        $html = view('exports.academy-monitoring-excel', [
            'rows' => $this->exportRows($request, $filters),
            'assessmentTitle' => AcademyAssessment::find($filters['assessment_id'])?->title ?? 'Semua Assessment',
        ])->render();

        return response()->streamDownload(
            fn () => print($html),
            'Monitoring_Evaluasi_Kompetensi_'.now()->format('Ymd_His').'.xls',
            ['Content-Type' => 'application/vnd.ms-excel']
        );
    }

    public function exportPdf(Request $request): View
    {
        $filters = $this->monitoringService->normalizeFilters($request->query());

        return view('exports.academy-monitoring-print', [
            'rows' => $this->exportRows($request, $filters),
            'summary' => $this->monitoringService->summary($filters['assessment_id']),
        ]);
    }

    private function exportRows(Request $request, array $filters): array
    {
        return AcademyMonitoringResource::collection($this->monitoringService->allForExport($filters))->resolve($request);
    }
}
