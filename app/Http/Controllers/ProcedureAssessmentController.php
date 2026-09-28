<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveProcedureQuestionRequest;
use App\Http\Requests\StoreProcedureAssessmentRequest;
use App\Http\Requests\UpdateProcedureAssessmentRequest;
use App\Models\ProcedureAssessment;
use App\Models\ProcedureQuestion;
use App\Services\ProcedureAssessmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** Aksi tab Assessment Evaluasi Prosedur (assessment, target distribusi, bank soal, TTD sertifikat). */
class ProcedureAssessmentController extends Controller
{
    public function __construct(private ProcedureAssessmentService $assessmentService) {}

    public function store(StoreProcedureAssessmentRequest $request): RedirectResponse
    {
        $assessment = $this->assessmentService->create($request->validated(), (string) $request->user()->name);

        return $this->toManage($assessment, 'Assessment berhasil dibuat');
    }

    public function update(UpdateProcedureAssessmentRequest $request, ProcedureAssessment $assessment): RedirectResponse
    {
        $this->assessmentService->updateSettings($assessment, $request->validated());

        return back()->with('success', 'Settings tersimpan');
    }

    public function publish(ProcedureAssessment $assessment): RedirectResponse
    {
        Gate::authorize('manage-procedure-evaluation');

        $count = $this->assessmentService->publish($assessment);

        return $this->toManage($assessment, "Assessment dipublish, {$count} notifikasi dikirim");
    }

    public function retrigger(ProcedureAssessment $assessment): RedirectResponse
    {
        Gate::authorize('manage-procedure-evaluation');

        $count = $this->assessmentService->distribute($assessment);

        return $this->toManage($assessment, "Notifikasi dikirim ulang ke {$count} karyawan");
    }

    public function archive(ProcedureAssessment $assessment): RedirectResponse
    {
        Gate::authorize('manage-procedure-evaluation');

        $this->assessmentService->archive($assessment);

        return redirect()->route('one-procedure.evaluasi', ['tab' => 'assessment'])->with('success', 'Assessment diarsipkan');
    }

    public function saveTargets(Request $request, ProcedureAssessment $assessment): RedirectResponse
    {
        Gate::authorize('manage-procedure-evaluation');
        $roles = $request->validate(['target_roles' => ['nullable', 'array'], 'target_roles.*' => ['string']])['target_roles'] ?? [];

        $this->assessmentService->saveTargets($assessment, $roles);

        return back()->with('success', 'Target distribusi tersimpan ('.count($roles).' role)');
    }

    public function destroy(ProcedureAssessment $assessment): RedirectResponse
    {
        Gate::authorize('manage-procedure-evaluation');

        $this->assessmentService->delete($assessment);

        return redirect()->route('one-procedure.evaluasi', ['tab' => 'assessment'])->with('success', 'Assessment dihapus');
    }

    /** TTD manual sertifikat oleh penandatangan yang ditetapkan di assessment. */
    public function sign(Request $request, ProcedureAssessment $assessment): RedirectResponse
    {
        abort_unless($assessment->signed_by && $assessment->signed_by === $request->user()->employee_id, 403, 'Anda bukan penandatangan yang ditetapkan');
        $signature = $request->validate(['signature' => ['required', 'string', 'starts_with:data:image/']])['signature'];

        $this->assessmentService->sign($assessment, $signature);

        return back()->with('success', 'Sertifikat berhasil ditandatangani');
    }

    public function storeQuestion(SaveProcedureQuestionRequest $request, ProcedureAssessment $assessment): RedirectResponse
    {
        $this->assessmentService->saveQuestion($assessment, $request->validated());

        return back()->with('success', 'Soal berhasil ditambahkan');
    }

    public function updateQuestion(SaveProcedureQuestionRequest $request, ProcedureQuestion $question): RedirectResponse
    {
        $this->assessmentService->saveQuestion($question->assessment, $request->validated(), $question);

        return back()->with('success', 'Soal berhasil diperbarui');
    }

    public function destroyQuestion(ProcedureQuestion $question): RedirectResponse
    {
        Gate::authorize('manage-procedure-evaluation');

        $question->delete();

        return back()->with('success', 'Soal dihapus');
    }

    private function toManage(ProcedureAssessment $assessment, string $message): RedirectResponse
    {
        return redirect()->route('one-procedure.evaluasi', ['tab' => 'assessment', 'manage' => $assessment->id])->with('success', $message);
    }
}
