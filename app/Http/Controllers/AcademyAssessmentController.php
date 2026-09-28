<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveAcademyQuestionRequest;
use App\Http\Requests\StoreAcademyAssessmentRequest;
use App\Http\Requests\UpdateAcademyAssessmentRequest;
use App\Models\AcademyAssessment;
use App\Models\AcademyQuestion;
use App\Services\AcademyAssessmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** Aksi tab Assessment Evaluasi Kompetensi (assessment, target distribusi, bank soal). */
class AcademyAssessmentController extends Controller
{
    public function __construct(private AcademyAssessmentService $assessmentService) {}

    public function store(StoreAcademyAssessmentRequest $request): RedirectResponse
    {
        $assessment = $this->assessmentService->create($request->validated(), (string) $request->user()->name);

        return $this->toManage($assessment, 'Assessment berhasil dibuat');
    }

    public function update(UpdateAcademyAssessmentRequest $request, AcademyAssessment $assessment): RedirectResponse
    {
        $this->assessmentService->updateSettings($assessment, $request->validated());

        return back()->with('success', 'Settings tersimpan');
    }

    public function publish(AcademyAssessment $assessment): RedirectResponse
    {
        Gate::authorize('manage-academy-evaluation');

        $count = $this->assessmentService->publish($assessment);

        return $this->toManage($assessment, "Assessment dipublish, {$count} notifikasi dikirim");
    }

    public function retrigger(AcademyAssessment $assessment): RedirectResponse
    {
        Gate::authorize('manage-academy-evaluation');

        $count = $this->assessmentService->distribute($assessment);

        return $this->toManage($assessment, "Notifikasi dikirim ulang ke {$count} karyawan");
    }

    public function archive(AcademyAssessment $assessment): RedirectResponse
    {
        Gate::authorize('manage-academy-evaluation');

        $this->assessmentService->archive($assessment);

        return redirect()->route('one-academy.evaluasi-kompetensi.index', ['tab' => 'assessment'])->with('success', 'Assessment diarsipkan');
    }

    public function saveTargets(Request $request, AcademyAssessment $assessment): RedirectResponse
    {
        Gate::authorize('manage-academy-evaluation');
        $roles = $request->validate(['target_roles' => ['nullable', 'array'], 'target_roles.*' => ['string']])['target_roles'] ?? [];

        $this->assessmentService->saveTargets($assessment, $roles);

        return back()->with('success', 'Target distribusi tersimpan ('.count($roles).' role)');
    }

    public function destroy(AcademyAssessment $assessment): RedirectResponse
    {
        Gate::authorize('manage-academy-evaluation');

        $this->assessmentService->delete($assessment);

        return redirect()->route('one-academy.evaluasi-kompetensi.index', ['tab' => 'assessment'])->with('success', 'Assessment dihapus');
    }

    public function storeQuestion(SaveAcademyQuestionRequest $request, AcademyAssessment $assessment): RedirectResponse
    {
        $this->assessmentService->saveQuestion($assessment, $request->validated());

        return back()->with('success', 'Soal berhasil ditambahkan');
    }

    public function updateQuestion(SaveAcademyQuestionRequest $request, AcademyQuestion $question): RedirectResponse
    {
        $this->assessmentService->saveQuestion($question->assessment, $request->validated(), $question);

        return back()->with('success', 'Soal berhasil diperbarui');
    }

    public function destroyQuestion(AcademyQuestion $question): RedirectResponse
    {
        Gate::authorize('manage-academy-evaluation');

        $this->assessmentService->deleteQuestion($question);

        return back()->with('success', 'Soal dihapus');
    }

    private function toManage(AcademyAssessment $assessment, string $message): RedirectResponse
    {
        return redirect()->route('one-academy.evaluasi-kompetensi.index', ['tab' => 'assessment', 'manage' => $assessment->id])->with('success', $message);
    }
}
