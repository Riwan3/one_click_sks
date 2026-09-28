<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProcedureCoachingRequest;
use App\Http\Resources\ProcedureMonitoringResource;
use App\Models\ProcedureAssessment;
use App\Models\ProcedureEvaluation;
use App\Models\ProcedureFollowup;
use App\Services\ProcedureMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Aksi tab Monitoring di Evaluasi Prosedur (detail, tindak lanjut, export, hapus).
 * Data halaman dirender oleh OneProcedureController@evaluasi.
 */
class ProcedureMonitoringController extends Controller
{
    public function __construct(private ProcedureMonitoringService $monitoringService) {}

    public function show(ProcedureEvaluation $evaluation): JsonResponse
    {
        return response()->json($this->monitoringService->detail($evaluation));
    }

    public function grantRemedial(ProcedureEvaluation $evaluation): RedirectResponse
    {
        Gate::authorize('manage-procedure-evaluation');

        $this->monitoringService->grantDirectRemedial($evaluation, $this->actorName());

        return back()->with('success', 'Akses remedial berhasil diberikan. Karyawan bisa mengulang assessment.');
    }

    public function scheduleCoaching(StoreProcedureCoachingRequest $request, ProcedureEvaluation $evaluation): RedirectResponse
    {
        $this->monitoringService->scheduleCoaching($evaluation, $request->validated(), $this->actorName());

        return back()->with('success', 'Jadwal coaching & mentoring berhasil disimpan.');
    }

    public function grantFollowupRemedial(ProcedureFollowup $followup): RedirectResponse
    {
        Gate::authorize('manage-procedure-evaluation');

        $isGranted = $this->monitoringService->grantFollowupRemedial($followup, $this->actorName());

        return $isGranted
            ? back()->with('success', 'Akses remedial berhasil diberikan setelah coaching.')
            : back()->with('error', 'Entry ini sudah pernah diberikan akses remedial sebelumnya.');
    }

    public function destroy(ProcedureAssessment $assessment, string $nrp): RedirectResponse
    {
        Gate::authorize('manage-procedure-evaluation');

        $this->monitoringService->removeAssignment($assessment->id, $nrp);

        return back()->with('success', 'Karyawan berhasil dihapus dari assessment ini.');
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $filters = $this->monitoringService->normalizeFilters($request->query());
        $html = view('exports.procedure-monitoring-excel', [
            'rows' => $this->exportRows($request, $filters),
            'assessmentTitle' => ProcedureAssessment::find($filters['assessment_id'])?->title ?? 'Semua Assessment',
        ])->render();

        return response()->streamDownload(
            fn () => print($html),
            'Monitoring_Evaluasi_'.now()->format('Ymd_His').'.xls',
            ['Content-Type' => 'application/vnd.ms-excel']
        );
    }

    public function exportPdf(Request $request): View
    {
        $filters = $this->monitoringService->normalizeFilters($request->query());

        return view('exports.procedure-monitoring-print', [
            'rows' => $this->exportRows($request, $filters),
            'summary' => $this->monitoringService->summary($filters['assessment_id']),
        ]);
    }

    private function exportRows(Request $request, array $filters): array
    {
        return ProcedureMonitoringResource::collection($this->monitoringService->allForExport($filters))->resolve($request);
    }

    private function actorName(): string
    {
        return (string) (auth()->user()?->name ?? '');
    }
}
