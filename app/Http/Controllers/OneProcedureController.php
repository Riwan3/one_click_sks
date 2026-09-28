<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveEvalProcedureRequest;
use App\Http\Resources\EvalProcedureResource;
use App\Http\Resources\ProcedureAssessmentResource;
use App\Http\Resources\ProcedureCertificateResource;
use App\Http\Resources\ProcedureMonitoringResource;
use App\Http\Resources\ProcedureQuestionResource;
use App\Http\Resources\ProsedurDocumentResource;
use App\Models\Procedure;
use App\Models\ProcedureAssessment;
use App\Models\ProcedureEvaluation;
use App\Models\ProsedurCategory;
use App\Models\ProsedurKerja;
use App\Models\ProsedurType;
use App\Services\EvalProcedureService;
use App\Services\OneProcedureDashboardService;
use App\Services\ProcedureAssessmentService;
use App\Services\ProcedureCertificateService;
use App\Services\ProcedureMonitoringService;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OneProcedureController extends Controller
{
    public function __construct(
        private ProcedureMonitoringService $monitoringService,
        private EvalProcedureService $evalProcedureService,
        private ProcedureAssessmentService $assessmentService,
        private ProcedureCertificateService $certificateService,
        private OneProcedureDashboardService $dashboardService,
    ) {}

    /**
     * Dashboard One-Procedure
     */
    public function dashboard(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('OneProcedure/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_procedure_menu'),
            'search' => $search,
            'documents' => $this->dashboardService->documentSummary(),
            'activities' => $this->dashboardService->activitySummary(),
            'evaluation' => $this->dashboardService->evaluationSummary(),
            'recentDocuments' => ProsedurDocumentResource::collection($this->dashboardService->recentDocuments($search))->resolve(),
        ]);
    }

    /**
     * 1. Prosedur (Dokumen Prosedur Kerja / SOP / IK)
     */
    public function prosedur(Request $request): Response
    {
        $categories = ProsedurCategory::orderBy('created_at', 'asc')->get();
        $types = ProsedurType::orderBy('created_at', 'asc')->get();

        $validTypes = ['aktivity_select'];
        foreach ($types as $t) {
            $validTypes[] = $t->slug;
        }
        foreach ($categories as $cat) {
            foreach ($types as $t) {
                $validTypes[] = $cat->slug . '_' . $t->slug;
            }
        }

        $jenis = (string) $request->query('type', '');
        if (!in_array($jenis, $validTypes, true)) {
            $jenis = $types->first()?->slug ?: 'sop';
        }

        $search = trim((string) $request->query('search', ''));
        $searchActive = !empty($search);

        $aktivityCategories = [];
        foreach ($categories as $cat) {
            $aktivityCategories[$cat->slug] = $cat->name;
        }
        $docTypeMap = [];
        foreach ($types as $t) {
            $docTypeMap[$t->slug] = $t->name;
        }

        $currentCategory = '';
        foreach ($aktivityCategories as $slug => $label) {
            if (str_starts_with($jenis, $slug)) {
                $currentCategory = $slug;
                break;
            }
        }

        $isAktivityContext = ($jenis === 'aktivity_select' || !empty($currentCategory));
        $showTable = !$isAktivityContext || (str_contains($jenis, '_') && $jenis !== 'aktivity_select');

        if (!empty($currentCategory)) {
            $subSlug = str_starts_with($jenis, $currentCategory . '_') ? substr($jenis, strlen($currentCategory) + 1) : '';
            $typeName = $docTypeMap[$subSlug] ?? $subSlug;
            $currentLabel = strtoupper(($aktivityCategories[$currentCategory] ?? $currentCategory) . ($typeName ? ' - ' . $typeName : ''));
        } else {
            $currentLabel = strtoupper($docTypeMap[$jenis] ?? str_replace('_', ' ', $jenis));
        }

        // Dokumen query sesuai SopRepository
        $docQuery = ProsedurKerja::query();

        if ($jenis && $jenis !== 'aktivity_select') {
            if (!str_contains($jenis, '_')) {
                $docQuery->where(function ($q) use ($jenis) {
                    $q->where('jenis', $jenis)
                      ->orWhere('jenis', 'like', "%_{$jenis}");
                });
            } else {
                $matchedCat = $categories->first(fn ($c) => str_starts_with($jenis, $c->slug . '_'));
                if ($matchedCat) {
                    $typePart = substr($jenis, strlen($matchedCat->slug) + 1);
                    $docQuery->where(function ($q) use ($jenis, $matchedCat, $typePart) {
                        $q->where('jenis', $jenis)
                          ->orWhereIn('id', function ($sub) use ($matchedCat, $typePart) {
                              $sub->select('pcd.prosedur_id')
                                  ->from('prosedur_category_docs as pcd')
                                  ->join('prosedur_kerja as pk2', 'pk2.id', '=', 'pcd.prosedur_id')
                                  ->where('pcd.category_id', $matchedCat->id)
                                  ->where(function ($q2) use ($typePart) {
                                      $q2->where('pk2.jenis', $typePart)
                                         ->orWhere('pk2.jenis', 'like', "%_{$typePart}");
                                  });
                          });
                    });
                } else {
                    $docQuery->where('jenis', $jenis);
                }
            }
        }

        if ($searchActive) {
            $docQuery->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('kode_dokumen', 'like', "%{$search}%");
            });
        }

        $docs = $docQuery->orderBy('created_at', 'desc')->get()->map(function ($d) use ($aktivityCategories, $docTypeMap, $isAktivityContext) {
            $docJenis = $d->jenis ?? '';
            $dCat = $isAktivityContext ? '-' : 'PROSEDUR (UMUM)';
            $dType = $docJenis;

            foreach ($aktivityCategories as $slug => $name) {
                if ($slug && (str_starts_with($docJenis, $slug . '_') || $docJenis === $slug)) {
                    $dCat = $name;
                    $dType = str_starts_with($docJenis, $slug . '_') ? substr($docJenis, strlen($slug) + 1) : '';
                    break;
                }
            }
            $dTypeLabel = $docTypeMap[$dType] ?? strtoupper($dType ?: '-');

            return [
                'id' => $d->id,
                'jenis' => $d->jenis,
                'kode_dokumen' => $d->kode_dokumen,
                'judul' => $d->judul,
                'tanggal' => $d->tanggal ? $d->tanggal->format('Y-m-d') : null,
                'file_dokumen' => $d->file_dokumen,
                'status' => $d->status ?? 'aktif',
                'category_label' => $dCat,
                'type_label' => $dTypeLabel,
                'created_at' => $d->created_at ? $d->created_at->format('Y-m-d') : null,
            ];
        });

        $counts = ProsedurKerja::where('status', 'aktif')
            ->selectRaw('jenis, COUNT(*) as total')
            ->groupBy('jenis')
            ->pluck('total', 'jenis')
            ->toArray();

        $allDocsForSelect = ProsedurKerja::select('id', 'jenis', 'kode_dokumen', 'judul')
            ->orderBy('judul', 'asc')
            ->get();

        return Inertia::render('OneProcedure/Prosedur', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_procedure_menu'),
            'docs' => $docs,
            'jenis' => $jenis,
            'search' => $search,
            'searchActive' => $searchActive,
            'currentLabel' => $currentLabel,
            'currentCategory' => $currentCategory,
            'isAktivityContext' => $isAktivityContext,
            'showTable' => $showTable,
            'counts' => $counts,
            'allSopCategories' => $categories,
            'allSopTypes' => $types,
            'allDocsForSelect' => $allDocsForSelect,
        ]);
    }

    public function storeProcedure(Request $request): RedirectResponse
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'kode_dokumen' => 'required|string|max:100',
            'file_dokumen' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        $catSlug = trim((string) $request->input('category_slug', ''));
        $typeSlug = trim((string) $request->input('type_slug', 'sop'));
        $jenis = (!empty($catSlug) && $catSlug !== 'global') ? ($catSlug . '_' . $typeSlug) : $typeSlug;

        $filePath = null;
        if ($request->hasFile('file_dokumen')) {
            $file = $request->file('file_dokumen');
            $filename = 'pk_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destDir = public_path('assets/uploads/prosedur_kerja');
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $file->move($destDir, $filename);
            $filePath = '/assets/uploads/prosedur_kerja/' . $filename;
        }

        ProsedurKerja::create([
            'jenis' => $jenis,
            'kode_dokumen' => trim($request->input('kode_dokumen')),
            'judul' => trim($request->input('judul')),
            'tanggal' => now()->toDateString(),
            'file_dokumen' => $filePath,
            'status' => $request->input('status', 'aktif'),
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Dokumen prosedur berhasil ditambahkan');
    }

    public function updateProcedure(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'kode_dokumen' => 'required|string|max:100',
            'file_dokumen' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        $doc = ProsedurKerja::findOrFail($id);

        $catSlug = trim((string) $request->input('category_slug', 'global'));
        $typeSlug = trim((string) $request->input('type_slug', 'sop'));
        $jenis = ($catSlug !== 'global' && !empty($catSlug)) ? ($catSlug . '_' . $typeSlug) : $typeSlug;

        $filePath = $doc->file_dokumen;
        if ($request->hasFile('file_dokumen')) {
            if ($doc->file_dokumen && file_exists(public_path(ltrim($doc->file_dokumen, '/')))) {
                @unlink(public_path(ltrim($doc->file_dokumen, '/')));
            }
            $file = $request->file('file_dokumen');
            $filename = 'pk_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destDir = public_path('assets/uploads/prosedur_kerja');
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $file->move($destDir, $filename);
            $filePath = '/assets/uploads/prosedur_kerja/' . $filename;
        }

        $doc->update([
            'jenis' => $jenis,
            'kode_dokumen' => trim($request->input('kode_dokumen')),
            'judul' => trim($request->input('judul')),
            'file_dokumen' => $filePath,
            'status' => $request->input('status', 'aktif'),
        ]);

        return back()->with('success', 'Dokumen prosedur berhasil diperbarui');
    }

    public function deleteProcedure($id): RedirectResponse
    {
        $doc = ProsedurKerja::find($id);
        if ($doc) {
            if ($doc->file_dokumen && file_exists(public_path(ltrim($doc->file_dokumen, '/')))) {
                @unlink(public_path(ltrim($doc->file_dokumen, '/')));
            }
            $doc->delete();
        }

        return back()->with('success', 'Dokumen prosedur berhasil dihapus');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:255']);
        $name = trim($request->input('name'));
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $name)));

        $category = ProsedurCategory::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );

        $docIds = $request->input('doc_ids', []);
        if (is_array($docIds) && !empty($docIds)) {
            $category->documents()->sync(array_filter(array_map('intval', $docIds)));
        }

        return back()->with('success', 'Kategori prosedur berhasil ditambahkan');
    }

    public function deleteCategory($id): RedirectResponse
    {
        $category = ProsedurCategory::find($id);
        if ($category) {
            $category->documents()->detach();
            $category->delete();
        }

        return back()->with('success', 'Kategori berhasil dihapus');
    }

    public function storeType(Request $request): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:255']);
        $name = trim($request->input('name'));
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $name)));

        ProsedurType::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );

        return back()->with('success', 'Jenis dokumen berhasil ditambahkan');
    }

    public function deleteType($id): RedirectResponse
    {
        ProsedurType::destroy($id);
        return back()->with('success', 'Jenis dokumen berhasil dihapus');
    }

    public function viewDocument($id): BinaryFileResponse
    {
        $doc = ProsedurKerja::findOrFail($id);
        if (!$doc->file_dokumen) {
            abort(404, 'File dokumen tidak ditemukan');
        }

        $localPath = public_path(ltrim($doc->file_dokumen, '/'));
        if (!file_exists($localPath)) {
            $legacyPath = 'd:/laragon/www/one-click/' . ltrim($doc->file_dokumen, '/');
            if (file_exists($legacyPath)) {
                $localPath = $legacyPath;
            } else {
                abort(404, 'File tidak ditemukan di server');
            }
        }

        $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
        $contentType = match ($ext) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'application/octet-stream',
        };

        return response()->file($localPath, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="' . basename($localPath) . '"',
        ]);
    }

    /**
     * 2. Activity (Dokumen Aktivity / Kategori Prosedur)
     * Sesuai dengan index?action=procedure_activity di legacy ../one-click
     */
    public function activity(Request $request): Response
    {
        $categories = ProsedurCategory::orderBy('created_at', 'asc')->get();
        $types = ProsedurType::orderBy('created_at', 'asc')->get();

        $validTypes = ['aktivity_select'];
        foreach ($types as $t) {
            $validTypes[] = $t->slug;
        }
        foreach ($categories as $cat) {
            foreach ($types as $t) {
                $validTypes[] = $cat->slug . '_' . $t->slug;
            }
        }

        $jenis = (string) $request->query('type', 'aktivity_select');
        if (!in_array($jenis, $validTypes, true)) {
            $jenis = 'aktivity_select';
        }

        $search = trim((string) $request->query('search', ''));
        $searchActive = !empty($search);

        $aktivityCategories = [];
        foreach ($categories as $cat) {
            $aktivityCategories[$cat->slug] = $cat->name;
        }
        $docTypeMap = [];
        foreach ($types as $t) {
            $docTypeMap[$t->slug] = $t->name;
        }

        $currentCategory = '';
        foreach ($aktivityCategories as $slug => $label) {
            if (str_starts_with($jenis, $slug)) {
                $currentCategory = $slug;
                break;
            }
        }

        $isAktivityContext = true;
        $showTable = !empty($currentCategory) && str_contains($jenis, '_') && $jenis !== 'aktivity_select';

        if (!empty($currentCategory)) {
            $subSlug = str_starts_with($jenis, $currentCategory . '_') ? substr($jenis, strlen($currentCategory) + 1) : '';
            $typeName = $docTypeMap[$subSlug] ?? $subSlug;
            $currentLabel = strtoupper(($aktivityCategories[$currentCategory] ?? $currentCategory) . ($typeName ? ' - ' . $typeName : ''));
        } else {
            $currentLabel = 'AKTIVITY';
        }

        // Dokumen query sesuai SopRepository
        $docQuery = ProsedurKerja::query();

        if ($jenis && $jenis !== 'aktivity_select') {
            if (!str_contains($jenis, '_')) {
                $docQuery->where(function ($q) use ($jenis) {
                    $q->where('jenis', $jenis)
                      ->orWhere('jenis', 'like', "%_{$jenis}");
                });
            } else {
                $matchedCat = $categories->first(fn ($c) => str_starts_with($jenis, $c->slug . '_'));
                if ($matchedCat) {
                    $typePart = substr($jenis, strlen($matchedCat->slug) + 1);
                    $docQuery->where(function ($q) use ($jenis, $matchedCat, $typePart) {
                        $q->where('jenis', $jenis)
                          ->orWhereIn('id', function ($sub) use ($matchedCat, $typePart) {
                              $sub->select('pcd.prosedur_id')
                                  ->from('prosedur_category_docs as pcd')
                                  ->join('prosedur_kerja as pk2', 'pk2.id', '=', 'pcd.prosedur_id')
                                  ->where('pcd.category_id', $matchedCat->id)
                                  ->where(function ($q2) use ($typePart) {
                                      $q2->where('pk2.jenis', $typePart)
                                         ->orWhere('pk2.jenis', 'like', "%_{$typePart}");
                                  });
                          });
                    });
                } else {
                    $docQuery->where('jenis', $jenis);
                }
            }
        }

        if ($searchActive) {
            $docQuery->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('kode_dokumen', 'like', "%{$search}%");
            });
        }

        $docs = $docQuery->orderBy('created_at', 'desc')->get()->map(function ($d) use ($aktivityCategories, $docTypeMap) {
            $docJenis = $d->jenis ?? '';
            $dCat = '-';
            $dType = $docJenis;

            foreach ($aktivityCategories as $slug => $name) {
                if ($slug && (str_starts_with($docJenis, $slug . '_') || $docJenis === $slug)) {
                    $dCat = $name;
                    $dType = str_starts_with($docJenis, $slug . '_') ? substr($docJenis, strlen($slug) + 1) : '';
                    break;
                }
            }
            $dTypeLabel = $docTypeMap[$dType] ?? strtoupper($dType ?: '-');

            return [
                'id' => $d->id,
                'jenis' => $d->jenis,
                'kode_dokumen' => $d->kode_dokumen,
                'judul' => $d->judul,
                'tanggal' => $d->tanggal ? $d->tanggal->format('Y-m-d') : null,
                'file_dokumen' => $d->file_dokumen,
                'status' => $d->status ?? 'aktif',
                'category_label' => $dCat,
                'type_label' => $dTypeLabel,
                'created_at' => $d->created_at ? $d->created_at->format('Y-m-d') : null,
            ];
        });

        $counts = ProsedurKerja::where('status', 'aktif')
            ->selectRaw('jenis, COUNT(*) as total')
            ->groupBy('jenis')
            ->pluck('total', 'jenis')
            ->toArray();

        $activeCategoryObj = null;
        $attachedDocIds = [];
        if ($currentCategory) {
            $activeCategoryObj = $categories->firstWhere('slug', $currentCategory);
            if ($activeCategoryObj) {
                $attachedDocIds = $activeCategoryObj->documents()->pluck('prosedur_kerja.id')->toArray();
            }
        }

        $allDocsForSelect = ProsedurKerja::select('id', 'jenis', 'kode_dokumen', 'judul', 'tanggal', 'status')
            ->orderBy('judul', 'asc')
            ->get();

        return Inertia::render('OneProcedure/Activity', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_procedure_menu'),
            'docs' => $docs,
            'jenis' => $jenis,
            'search' => $search,
            'searchActive' => $searchActive,
            'currentLabel' => $currentLabel,
            'currentCategory' => $currentCategory,
            'activeCategory' => $activeCategoryObj,
            'attachedDocIds' => $attachedDocIds,
            'isAktivityContext' => $isAktivityContext,
            'showTable' => $showTable,
            'counts' => $counts,
            'allSopCategories' => $categories,
            'allSopTypes' => $types,
            'allDocsForSelect' => $allDocsForSelect,
        ]);
    }

    public function syncCategoryDocs(Request $request): RedirectResponse
    {
        $request->validate([
            'category_id' => 'required|exists:prosedur_categories,id',
            'doc_ids' => 'nullable|array',
            'doc_ids.*' => 'integer',
        ]);

        $category = ProsedurCategory::findOrFail($request->input('category_id'));
        $docIds = array_filter(array_map('intval', (array) $request->input('doc_ids', [])));

        $category->documents()->sync($docIds);

        return back()->with('success', 'Dokumen aktifitas ' . $category->name . ' berhasil diperbarui');
    }

    /**
     * 3. Evaluasi Prosedur (SOP Evaluation, Assessment, Monitoring & Certification)
     * Sesuai dengan database/sop/evaluasi.php?tab=procedure di legacy ../one-click
     */
    public function evaluasi(Request $request): Response
    {
        $request->user()?->loadMissing('employee');
        $isAdminOrGL = Gate::allows('manage-procedure-evaluation');

        $activeTab = $request->query('tab', 'procedure');
        // Filter prosedur hanya berlaku di tab Procedure (param status/search tab lain punya arti berbeda)
        $filters = collect(['search', 'cat', 'risk', 'status', 'date_from', 'date_to'])
            ->mapWithKeys(fn ($key) => [$key => $activeTab === 'procedure' ? trim((string) $request->query($key, '')) : ''])
            ->all();

        $this->evalProcedureService->autoExpire();
        $procedures = in_array($activeTab, ['procedure', 'assessment'], true)
            ? EvalProcedureResource::collection($this->evalProcedureService->list($filters))->resolve()
            : [];
        $categories = $this->evalProcedureService->categories();

        $monitoring = $activeTab === 'monitoring' ? $this->monitoringProps($request) : null;

        return Inertia::render('OneProcedure/Evaluasi', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_procedure_menu'),
            'isAdminOrGL' => $isAdminOrGL,
            'activeTab' => $activeTab,
            'filters' => $filters,
            'procedures' => $procedures,
            'categories' => $categories,
            'assessment' => $activeTab === 'assessment' ? $this->assessmentProps($request) : null,
            'monitoring' => $monitoring,
            'certification' => $activeTab === 'certification' ? $this->certificationProps($request) : null,
        ]);
    }

    private function assessmentProps(Request $request): array
    {
        $manageId = (int) $request->query('manage', 0);
        $manage = $manageId
            ? ProcedureAssessment::with(['procedure:id,title', 'questions' => fn ($q) => $q->orderBy('id')])->withCount('questions')->find($manageId)
            : null;
        $targets = $manage?->target_roles ?: [];

        return [
            'createMode' => $request->boolean('create'),
            'procId' => (int) $request->query('proc_id', 0),
            'focus' => (string) $request->query('focus', ''),
            'list' => ProcedureAssessmentResource::collection($this->assessmentService->listAll())->resolve(),
            'manage' => $manage ? (new ProcedureAssessmentResource($manage))->resolve() : null,
            'questions' => $manage ? ProcedureQuestionResource::collection($manage->questions)->resolve() : [],
            'stats' => $manage ? [
                'total_questions' => $manage->questions_count,
                'total_roles' => count($targets),
                'total_employees' => $this->assessmentService->estimatedEmployees($targets),
                'estimated_time' => $manage->timer_minutes,
                'passing_grade' => $manage->passing_grade,
            ] : null,
            'targetRoles' => $this->assessmentService->targetRoleOptions(),
            'signers' => $this->assessmentService->signerOptions(),
            'currentEmployeeId' => $request->user()?->employee_id,
        ];
    }

    private function certificationProps(Request $request): array
    {
        $this->certificateService->refreshStatuses();

        $filters = $this->certificateService->normalizeFilters($request->query());
        $paginator = $this->certificateService->paginate($filters);

        return [
            'filters' => $filters,
            'summary' => $this->certificateService->summary(),
            'assessments' => $this->certificateService->assessmentOptions(),
            'passedWithoutCertCount' => $this->certificateService->passedWithoutCertificate($filters['assessment_id'])
                ->select('assessment_id', 'nrp')->distinct()->get()->count(),
            'rows' => ProcedureCertificateResource::collection($paginator->items())->resolve(),
            'pagination' => [
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'perPage' => $paginator->perPage(),
            ],
        ];
    }

    private function monitoringProps(Request $request): array
    {
        $this->monitoringService->recalcPassedFlags();

        $filters = $this->monitoringService->normalizeFilters($request->query());
        $paginator = $this->monitoringService->paginate($filters);

        return [
            'filters' => $filters,
            'summary' => $this->monitoringService->summary($filters['assessment_id']),
            'dailyCompletions' => $this->monitoringService->dailyCompletions($filters['assessment_id']),
            'assessments' => $this->monitoringService->assessmentOptions(),
            'jabatans' => $this->monitoringService->jabatanOptions(),
            'mentors' => $this->monitoringService->mentorOptions(),
            'rows' => ProcedureMonitoringResource::collection($paginator->items())->resolve(),
            'pagination' => [
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'perPage' => $paginator->perPage(),
            ],
        ];
    }

    public function storeEvalProcedure(SaveEvalProcedureRequest $request): RedirectResponse
    {
        $this->evalProcedureService->create($request->validated(), $request->file('file'), (string) $request->user()->name);

        return redirect()->route('one-procedure.evaluasi', ['tab' => 'procedure'])->with('success', 'Prosedur berhasil ditambahkan');
    }

    public function updateEvalProcedure(SaveEvalProcedureRequest $request, Procedure $procedure): RedirectResponse
    {
        $this->evalProcedureService->update($procedure, $request->validated(), $request->file('file'));

        return redirect()->route('one-procedure.evaluasi', ['tab' => 'procedure'])->with('success', 'Prosedur berhasil diperbarui');
    }

    public function deleteEvalProcedure(Procedure $procedure): RedirectResponse
    {
        Gate::authorize('manage-procedure-evaluation');

        $this->evalProcedureService->delete($procedure);

        return redirect()->route('one-procedure.evaluasi', ['tab' => 'procedure'])->with('success', 'Prosedur berhasil dihapus');
    }

    public function viewEvalProcedure($id): BinaryFileResponse
    {
        $proc = Procedure::findOrFail($id);
        if (!$proc->file_path) {
            abort(404, 'File prosedur tidak ditemukan');
        }

        $localPath = public_path(ltrim($proc->file_path, '/'));
        if (!file_exists($localPath)) {
            $legacyPath = 'd:/laragon/www/one-click/' . ltrim($proc->file_path, '/');
            if (file_exists($legacyPath)) {
                $localPath = $legacyPath;
            } else {
                abort(404, 'File prosedur tidak ditemukan di server');
            }
        }

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
            'Content-Disposition' => 'inline; filename="' . basename($localPath) . '"',
        ]);
    }

    public function storeEvaluation(Request $request): RedirectResponse
    {
        Gate::authorize('manage-procedure-evaluation');
        $data = $request->validate([
            'assessment_id' => 'required|exists:procedure_assessments,id',
            'nrp' => 'required|string',
            'score' => 'required|integer|min:0|max:100',
        ]);

        $assessment = ProcedureAssessment::findOrFail($data['assessment_id']);
        $evaluation = ProcedureEvaluation::create([
            'assessment_id' => $assessment->id,
            'nrp' => $data['nrp'],
            'score' => $data['score'],
            'status' => $data['score'] >= $assessment->passing_grade ? 'passed' : 'failed',
            'completed_at' => now(),
            'notes' => $request->input('notes'),
        ]);
        $this->monitoringService->assignEmployee($assessment->id, $data['nrp']);

        // Sertifikat diterbitkan otomatis ketika karyawan lulus assessment (sama seperti v1)
        if ($evaluation->status === 'passed') {
            $this->certificateService->issue($evaluation, (string) $request->user()->name);
        }

        return back()->with('success', 'Data evaluasi prosedur berhasil disimpan');
    }
}
