<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveAcademyMateriRequest;
use App\Http\Resources\AcademyAssessmentResource;
use App\Http\Resources\AcademyMateriResource;
use App\Http\Resources\AcademyMonitoringResource;
use App\Http\Resources\AcademyQuestionResource;
use App\Models\AcademyAssessment;
use App\Models\AcademyMateri;
use App\Services\AcademyAssessmentService;
use App\Services\AcademyMateriService;
use App\Services\AcademyMonitoringService;
use App\Services\AcademyRaportService;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EvaluasiKompetensiController extends Controller
{
    public function __construct(
        private AcademyMateriService $materiService,
        private AcademyAssessmentService $assessmentService,
        private AcademyMonitoringService $monitoringService,
        private AcademyRaportService $raportService,
    ) {}

    public function index(Request $request): Response
    {
        $isAdminOrGL = Gate::allows('manage-academy-evaluation');

        $activeTab = $request->query('tab', 'materi');
        $filters = collect(['search', 'cat', 'risk', 'status', 'date_from', 'date_to'])
            ->mapWithKeys(fn ($key) => [$key => $activeTab === 'materi' ? trim((string) $request->query($key, '')) : ''])
            ->all();

        $this->materiService->autoExpire();
        $materiList = in_array($activeTab, ['materi', 'assessment'], true)
            ? AcademyMateriResource::collection($this->materiService->list($filters))->resolve()
            : [];

        return Inertia::render('OneAcademy/EvaluasiKompetensi/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_academy_menu'),
            'isAdminOrGL' => $isAdminOrGL,
            'activeTab' => $activeTab,
            'filters' => $filters,
            'materiList' => $materiList,
            'categories' => $this->materiService->categories(),
            'assessment' => $activeTab === 'assessment' ? $this->assessmentProps($request) : null,
            'monitoring' => $activeTab === 'monitoring' ? $this->monitoringProps($request) : null,
            'raport' => $activeTab === 'raport' ? $this->raportProps($request) : null,
        ]);
    }

    private function assessmentProps(Request $request): array
    {
        $manageId = (int) $request->query('manage', 0);
        $manage = $manageId
            ? AcademyAssessment::with('materi:id,title')->withCount('questions')->find($manageId)
            : null;
        $targets = $manage?->target_roles ?: [];

        return [
            'createMode' => $request->boolean('create'),
            'procId' => (int) $request->query('proc_id', 0),
            'focus' => (string) $request->query('focus', ''),
            'list' => AcademyAssessmentResource::collection($this->assessmentService->listAll())->resolve(),
            'manage' => $manage ? (new AcademyAssessmentResource($manage))->resolve() : null,
            'questions' => $manage ? AcademyQuestionResource::collection($manage->questions)->resolve() : [],
            'stats' => $manage ? [
                'total_questions' => $manage->questions_count,
                'total_roles' => count($targets),
                'total_employees' => $this->assessmentService->estimatedEmployees($targets),
                'estimated_time' => $manage->timer_minutes,
                'passing_grade' => $manage->passing_grade,
            ] : null,
            'targetRoles' => $this->assessmentService->targetRoleOptions(),
            'signers' => $this->assessmentService->signerOptions(),
        ];
    }

    private function monitoringProps(Request $request): array
    {
        $filters = $this->monitoringService->normalizeFilters($request->query());
        $paginator = $this->monitoringService->paginate($filters);

        return [
            'filters' => $filters,
            'summary' => $this->monitoringService->summary($filters['assessment_id']),
            'dailyCompletions' => $this->monitoringService->dailyCompletions($filters['assessment_id']),
            'assessments' => $this->monitoringService->assessmentOptions(),
            'jabatans' => $this->monitoringService->jabatanOptions(),
            'rows' => AcademyMonitoringResource::collection($paginator->items())->resolve(),
            'pagination' => [
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'perPage' => $paginator->perPage(),
            ],
        ];
    }

    private function raportProps(Request $request): array
    {
        $filters = ['jabatan' => (string) $request->query('jabatan', '')];
        $search = trim((string) $request->query('search', ''));

        return [
            'filters' => $filters,
            'search' => $search,
            'jabatans' => $this->raportService->jabatanOptions(),
            'assessments' => $this->raportService->assessmentOptions(),
            'rows' => $this->raportService->matrix($filters, $search),
        ];
    }

    public function storeMateri(SaveAcademyMateriRequest $request): RedirectResponse
    {
        $this->materiService->create($request->validated(), $request->file('file'), (string) $request->user()->name);

        return redirect()->route('one-academy.evaluasi-kompetensi.index', ['tab' => 'materi'])->with('success', 'Materi berhasil ditambahkan');
    }

    public function updateMateri(SaveAcademyMateriRequest $request, AcademyMateri $materi): RedirectResponse
    {
        $this->materiService->update($materi, $request->validated(), $request->file('file'));

        return redirect()->route('one-academy.evaluasi-kompetensi.index', ['tab' => 'materi'])->with('success', 'Materi berhasil diperbarui');
    }

    public function deleteMateri(AcademyMateri $materi): RedirectResponse
    {
        Gate::authorize('manage-academy-evaluation');

        $this->materiService->delete($materi);

        return redirect()->route('one-academy.evaluasi-kompetensi.index', ['tab' => 'materi'])->with('success', 'Materi berhasil dihapus');
    }

    public function viewMateri(AcademyMateri $materi): BinaryFileResponse
    {
        abort_unless($materi->file_path, 404, 'File materi tidak ditemukan');

        $localPath = public_path(ltrim($materi->file_path, '/'));
        abort_unless(file_exists($localPath), 404, 'File materi tidak ditemukan di server');

        $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
        $contentType = match ($ext) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };

        return response()->file($localPath, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="'.basename($localPath).'"',
        ]);
    }

    /** Input nilai manual (mis. dinilai instruktur/GL langsung, tanpa sesi ujian mandiri). */
    public function recordResult(Request $request): RedirectResponse
    {
        Gate::authorize('manage-academy-evaluation');
        $data = $request->validate([
            'assessment_id' => ['required', 'exists:academy_assessments,id'],
            'nrp' => ['required', 'string'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'time_taken' => ['nullable', 'integer', 'min:0'],
        ]);

        $assessment = AcademyAssessment::findOrFail($data['assessment_id']);
        $this->monitoringService->recordManualResult($assessment, $data['nrp'], (float) $data['score'], $data['time_taken'] ?? null);

        return back()->with('success', 'Hasil evaluasi berhasil disimpan');
    }

    public function raportDetail(string $nrp)
    {
        $data = $this->raportService->employeeDetail($nrp);
        abort_unless($data, 404);

        return response()->json($data);
    }
}
