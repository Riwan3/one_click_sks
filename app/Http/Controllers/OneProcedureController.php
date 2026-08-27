<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Procedure;
use App\Models\ProcedureAssessment;
use App\Models\ProcedureEvaluation;
use App\Models\ProcedureQuestion;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OneProcedureController extends Controller
{
    /**
     * Dashboard One-Procedure
     */
    public function dashboard(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $totalProcedures = Procedure::count();
        $activeProcedures = Procedure::where('status', 'active')->count();
        $totalAssessments = ProcedureAssessment::count();
        $totalEvaluations = ProcedureEvaluation::count();

        $recentProcedures = Procedure::withCount(['assessments'])
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('category', 'like', "%{$search}%"))
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return Inertia::render('OneProcedure/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_procedure_menu'),
            'search' => $search,
            'stats' => [
                'totalProcedures' => $totalProcedures,
                'activeProcedures' => $activeProcedures,
                'totalAssessments' => $totalAssessments,
                'totalEvaluations' => $totalEvaluations,
            ],
            'recentProcedures' => $recentProcedures,
        ]);
    }

    /**
     * 1. Prosedur (Dokumen & Materi SOP)
     */
    public function prosedur(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $category = $request->query('category', '');
        $risk = $request->query('risk', '');
        $status = $request->query('status', '');

        $query = Procedure::query()
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"))
            ->when($category !== '', fn ($q) => $q->where('category', $category))
            ->when($risk !== '', fn ($q) => $q->where('risk_level', $risk))
            ->when($status !== '', fn ($q) => $q->where('status', $status));

        $procedures = $query->orderBy('created_at', 'desc')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'title' => $p->title,
                'category' => $p->category,
                'revision' => $p->revision,
                'description' => $p->description,
                'risk_level' => $p->risk_level,
                'status' => $p->status,
                'file_path' => $p->file_path,
                'file_type' => $p->file_type,
                'effective_date' => $p->effective_date,
                'expired_date' => $p->expired_date,
                'created_by' => $p->created_by ?: 'System',
                'created_at' => $p->created_at->format('Y-m-d'),
                'assessments_count' => $p->assessments()->count(),
            ];
        });

        $categories = Procedure::distinct()->pluck('category')->filter()->values();

        return Inertia::render('OneProcedure/Prosedur', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_procedure_menu'),
            'filters' => [
                'search' => $search,
                'category' => $category,
                'risk' => $risk,
                'status' => $status,
            ],
            'procedures' => $procedures,
            'categories' => $categories,
        ]);
    }

    public function storeProcedure(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string',
            'category' => 'nullable|string',
            'risk_level' => 'required|in:low,medium,high,critical',
        ]);

        Procedure::create([
            'title' => $request->title,
            'category' => $request->category ?: 'Operasional',
            'revision' => $request->revision ?: 'Rev. 0',
            'description' => $request->description,
            'risk_level' => $request->risk_level,
            'status' => $request->status ?: 'active',
            'effective_date' => $request->effective_date ?: date('Y-m-d'),
            'expired_date' => $request->expired_date,
            'created_by' => auth()->user()?->name ?: 'Administrator',
        ]);

        return back()->with('success', 'Prosedur baru berhasil disimpan');
    }

    public function updateProcedure(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string',
            'risk_level' => 'required|in:low,medium,high,critical',
        ]);

        $p = Procedure::findOrFail($id);
        $p->update([
            'title' => $request->title,
            'category' => $request->category ?: $p->category,
            'revision' => $request->revision ?: $p->revision,
            'description' => $request->description,
            'risk_level' => $request->risk_level,
            'status' => $request->status ?: $p->status,
            'effective_date' => $request->effective_date ?: $p->effective_date,
            'expired_date' => $request->expired_date,
        ]);

        return back()->with('success', 'Data prosedur berhasil diperbarui');
    }

    public function deleteProcedure($id): RedirectResponse
    {
        Procedure::destroy($id);
        return back()->with('success', 'Prosedur berhasil dihapus');
    }

    /**
     * 2. Activity (Activity & Question Bank Assessment)
     */
    public function activity(Request $request): Response
    {
        $procedureId = $request->query('procedure_id');

        $procedures = Procedure::orderBy('title', 'asc')->get(['id', 'title', 'category']);

        $assessments = ProcedureAssessment::with(['procedure', 'questions'])
            ->when($procedureId, fn ($q) => $q->where('procedure_id', $procedureId))
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('OneProcedure/Activity', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_procedure_menu'),
            'selectedProcedureId' => $procedureId,
            'procedures' => $procedures,
            'assessments' => $assessments,
        ]);
    }

    public function storeAssessment(Request $request): RedirectResponse
    {
        $request->validate([
            'procedure_id' => 'required|exists:procedures,id',
            'title' => 'required|string',
            'passing_grade' => 'required|integer|min:0|max:100',
        ]);

        ProcedureAssessment::create([
            'procedure_id' => $request->procedure_id,
            'title' => $request->title,
            'passing_grade' => (int) $request->passing_grade,
            'timer_minutes' => (int) ($request->timer_minutes ?: 30),
            'retry_limit' => (int) ($request->retry_limit ?: 3),
            'validity_months' => (int) ($request->validity_months ?: 6),
            'status' => $request->status ?: 'draft',
            'created_by' => auth()->user()?->name ?: 'Administrator',
        ]);

        return back()->with('success', 'Activity / Assessment baru berhasil dibuat');
    }

    public function updateAssessment(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string',
            'passing_grade' => 'required|integer|min:0|max:100',
        ]);

        $pa = ProcedureAssessment::findOrFail($id);
        $pa->update([
            'title' => $request->title,
            'passing_grade' => (int) $request->passing_grade,
            'timer_minutes' => (int) ($request->timer_minutes ?: $pa->timer_minutes),
            'retry_limit' => (int) ($request->retry_limit ?: $pa->retry_limit),
            'validity_months' => (int) ($request->validity_months ?: $pa->validity_months),
            'status' => $request->status ?: $pa->status,
        ]);

        return back()->with('success', 'Activity / Assessment berhasil diperbarui');
    }

    public function deleteAssessment($id): RedirectResponse
    {
        ProcedureAssessment::destroy($id);
        return back()->with('success', 'Assessment berhasil dihapus');
    }

    public function storeQuestion(Request $request): RedirectResponse
    {
        $request->validate([
            'assessment_id' => 'required|exists:procedure_assessments,id',
            'question' => 'required|string',
            'correct' => 'required|string',
        ]);

        ProcedureQuestion::create([
            'assessment_id' => $request->assessment_id,
            'question_type' => $request->question_type ?: 'multiple_choice',
            'question' => $request->question,
            'option_a' => $request->option_a,
            'option_b' => $request->option_b,
            'option_c' => $request->option_c,
            'option_d' => $request->option_d,
            'correct' => strtoupper($request->correct),
            'explanation' => $request->explanation,
        ]);

        return back()->with('success', 'Soal berhasil ditambahkan ke bank soal');
    }

    public function deleteQuestion($id): RedirectResponse
    {
        ProcedureQuestion::destroy($id);
        return back()->with('success', 'Soal berhasil dihapus');
    }

    /**
     * 3. Evaluasi Prosedur (Assessment Results & Compliance Monitoring)
     */
    public function evaluasi(Request $request): Response
    {
        $assessmentId = $request->query('assessment_id');
        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $assessments = ProcedureAssessment::orderBy('title', 'asc')->get(['id', 'title']);

        $evaluations = ProcedureEvaluation::with(['assessment.procedure', 'employee'])
            ->when($assessmentId, fn ($q) => $q->where('assessment_id', $assessmentId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where('nrp', 'like', "%{$search}%")
                  ->orWhereHas('employee', fn ($eq) => $eq->where('name', 'like', "%{$search}%"));
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($e) {
                return [
                    'id' => $e->id,
                    'nrp' => $e->nrp,
                    'employee_name' => $e->employee ? $e->employee->name : 'NRP ' . $e->nrp,
                    'department' => $e->employee ? $e->employee->departemen : 'PRODUKSI',
                    'assessment_title' => $e->assessment ? $e->assessment->title : '-',
                    'procedure_title' => $e->assessment && $e->assessment->procedure ? $e->assessment->procedure->title : '-',
                    'score' => $e->score,
                    'passing_grade' => $e->assessment ? $e->assessment->passing_grade : 80,
                    'status' => $e->status,
                    'completed_at' => $e->completed_at ? $e->completed_at->format('Y-m-d H:i') : '-',
                    'certificate_code' => $e->certificate_code ?: '-',
                    'notes' => $e->notes,
                ];
            });

        $totalEvaluations = $evaluations->count();
        $passedCount = $evaluations->where('status', 'passed')->count();
        $failedCount = $evaluations->where('status', 'failed')->count();
        $passRate = $totalEvaluations > 0 ? round(($passedCount / $totalEvaluations) * 100, 1) : 0;

        return Inertia::render('OneProcedure/Evaluasi', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_procedure_menu'),
            'filters' => [
                'assessment_id' => $assessmentId,
                'status' => $status,
                'search' => $search,
            ],
            'stats' => [
                'totalEvaluations' => $totalEvaluations,
                'passedCount' => $passedCount,
                'failedCount' => $failedCount,
                'passRate' => $passRate,
            ],
            'assessments' => $assessments,
            'evaluations' => $evaluations,
        ]);
    }

    public function storeEvaluation(Request $request): RedirectResponse
    {
        $request->validate([
            'assessment_id' => 'required|exists:procedure_assessments,id',
            'nrp' => 'required|string',
            'score' => 'required|integer|min:0|max:100',
        ]);

        $assessment = ProcedureAssessment::findOrFail($request->assessment_id);
        $passingGrade = $assessment->passing_grade ?: 80;
        $score = (int) $request->score;
        $status = $score >= $passingGrade ? 'passed' : 'failed';
        $certCode = $status === 'passed' ? 'CERT-SOP-' . strtoupper(Str::random(6)) : null;

        ProcedureEvaluation::create([
            'assessment_id' => $request->assessment_id,
            'nrp' => $request->nrp,
            'score' => $score,
            'status' => $status,
            'completed_at' => now(),
            'certificate_code' => $certCode,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Data evaluasi prosedur berhasil disimpan');
    }

    public function deleteEvaluation($id): RedirectResponse
    {
        ProcedureEvaluation::destroy($id);
        return back()->with('success', 'Data evaluasi berhasil dihapus');
    }
}
