<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveOdrAssignmentRequest;
use App\Http\Resources\OdrAssignmentResource;
use App\Models\OdrAssignment;
use App\Services\OdrAssignmentService;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Daily Compliance (checklist task) & Assignment Harian One-Reporting. */
class OdrDailyComplianceController extends Controller
{
    public function __construct(private OdrAssignmentService $assignmentService) {}

    /**
     * API mobile: Daily Compliance (?tanggal=Y-m-d&shift=&area=&status=). Isi dan aturan akses sama
     * dengan halaman web; can_manage menentukan apakah checklist boleh diubah.
     */
    public function apiDaily(Request $request): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('access-compliance-report');

        $filters = $this->assignmentService->normalizeFilters($request->query());
        $this->assignmentService->syncStatuses($filters['tanggal']);

        return response()->json([
            'filters' => $filters,
            'kpi' => $this->assignmentService->kpi($filters['tanggal'], $filters['shift']),
            'rows' => OdrAssignmentResource::collection($this->assignmentService->list([...$filters, 'pengawas' => '']))->resolve(),
            'area_options' => $this->assignmentService->usedAreas(),
            'shift_options' => OdrAssignment::SHIFTS,
            'aktivitas_docs' => (object) $this->assignmentService->aktivitasDocuments(),
            'can_manage' => Gate::allows('manage-compliance-assignment'),
        ]);
    }

    /** API mobile: centang/lepas satu task checklist (hak akses sama dengan toggleTask web). */
    public function apiToggleTask(Request $request, OdrAssignment $assignment): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('manage-compliance-assignment');
        $data = $request->validate(['task_index' => ['required', 'integer', 'min:0'], 'value' => ['required', 'boolean']]);

        $compliance = $this->assignmentService->toggleTask($assignment, $data['task_index'], filter_var($data['value'], FILTER_VALIDATE_BOOLEAN));

        return response()->json([
            'row' => OdrAssignmentResource::make($assignment->fresh())->resolve(),
            'compliance' => $compliance,
        ]);
    }

    public function daily(Request $request): Response
    {
        Gate::authorize('access-compliance-report');

        $filters = $this->assignmentService->normalizeFilters($request->query());
        $this->assignmentService->syncStatuses($filters['tanggal']);

        return Inertia::render('OneReporting/DailyCompliance', [
            ...$this->commonProps(),
            'filters' => $filters,
            'kpi' => $this->assignmentService->kpi($filters['tanggal'], $filters['shift']),
            'rows' => OdrAssignmentResource::collection($this->assignmentService->list([...$filters, 'pengawas' => '']))->resolve(),
            'areaOptions' => $this->assignmentService->usedAreas(),
            'shiftOptions' => OdrAssignment::SHIFTS,
            'aktivitasDocs' => $this->assignmentService->aktivitasDocuments(),
            'canManage' => Gate::allows('manage-compliance-assignment'),
        ]);
    }

    public function toggleTask(Request $request, OdrAssignment $assignment): RedirectResponse
    {
        Gate::authorize('manage-compliance-assignment');
        $data = $request->validate(['task_index' => ['required', 'integer', 'min:0'], 'value' => ['required', 'boolean']]);

        $this->assignmentService->toggleTask($assignment, $data['task_index'], $data['value']);

        return back();
    }

    public function assignments(Request $request): Response
    {
        Gate::authorize('manage-compliance-assignment');

        $filters = $this->assignmentService->normalizeFilters($request->query());

        return Inertia::render('OneReporting/AssignmentHarian', [
            ...$this->commonProps(),
            'filters' => $filters,
            'rows' => OdrAssignmentResource::collection($this->assignmentService->list([...$filters, 'status' => '']))->resolve(),
            'areaOptions' => $this->assignmentService->areaOptions(),
            'unitOptions' => $this->assignmentService->unitOptions(),
            'aktivitasOptions' => $this->assignmentService->aktivitasOptions(),
            'pengawasOptions' => $this->assignmentService->pengawasOptions(),
            'shiftOptions' => OdrAssignment::SHIFTS,
        ]);
    }

    public function store(SaveOdrAssignmentRequest $request): RedirectResponse
    {
        $assignment = $this->assignmentService->create($request->validated());

        return $this->backToAssignments($assignment->tanggal, $assignment->shift, 'Data assignment berhasil ditambahkan.');
    }

    public function update(SaveOdrAssignmentRequest $request, OdrAssignment $assignment): RedirectResponse
    {
        $this->assignmentService->update($assignment, $request->validated());

        return $this->backToAssignments($assignment->tanggal, $assignment->shift, 'Data assignment berhasil diperbarui.');
    }

    public function destroy(OdrAssignment $assignment): RedirectResponse
    {
        Gate::authorize('manage-compliance-assignment');

        $assignment->delete();

        return back()->with('success', 'Data berhasil dihapus.');
    }

    public function template(): StreamedResponse
    {
        Gate::authorize('manage-compliance-assignment');

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, OdrAssignmentService::TEMPLATE_HEADERS);
            fputcsv($out, [now()->toDateString(), 'FRONT', 'EX-001', 'Nama Pengawas', 'Keterangan opsional', 'Shift 1']);
            fputcsv($out, [now()->toDateString(), 'DISPOSAL', 'DT-023', 'Nama Pengawas 2', '', 'Shift 2']);
            fclose($out);
        }, 'template_assignment_harian.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        Gate::authorize('manage-compliance-assignment');
        $data = $request->validate([
            'file_excel' => ['required', 'file', 'mimes:xlsx,csv,txt'],
            'tanggal_back' => ['nullable', 'date'],
            'shift_back' => ['nullable', 'string'],
        ], ['file_excel.mimes' => 'Format file tidak didukung. Gunakan .xlsx atau .csv.', 'file_excel.required' => 'Gagal upload file. Pastikan file dipilih dengan benar.']);

        $shift = (string) ($data['shift_back'] ?? '');
        $result = $this->assignmentService->import($request->file('file_excel'), $shift);
        $tanggal = $result['last_date'] ?? ($data['tanggal_back'] ?? now()->toDateString());

        if ($result['imported'] === 0) {
            return $this->backToAssignments($tanggal, $shift, 'Tidak ada data yang berhasil diimport. '.implode(' ', array_slice($result['errors'], 0, 3)), 'error');
        }

        $message = "{$result['imported']} data berhasil diimport.".($result['skipped'] > 0 ? " {$result['skipped']} baris dilewati." : '');

        return $this->backToAssignments($tanggal, $shift, $message);
    }

    private function backToAssignments(string $tanggal, string $shift, string $message, string $type = 'success'): RedirectResponse
    {
        return redirect()->route('one-reporting.assignments', array_filter(['tanggal' => $tanggal, 'shift' => $shift]))->with($type, $message);
    }

    private function commonProps(): array
    {
        return [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_reporting_menu'),
        ];
    }
}
