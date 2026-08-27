<?php

namespace App\Http\Controllers;

use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EvaluasiKompetensiController extends Controller
{
    public function index(Request $request): Response
    {
        $activeTab = $request->string('tab', 'materi')->toString();
        $search = trim($request->string('search')->toString());

        // Materi Tab Data
        $materiList = DB::table('academy_materi')
            ->when($search !== '', function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->get();

        // Assessment Tab Data
        $assessments = DB::table('academy_assessments as a')
            ->leftJoin('academy_materi as m', 'a.procedure_id', '=', 'm.id')
            ->select('a.*', 'm.title as procedure_title')
            ->orderBy('a.id', 'desc')
            ->get();

        foreach ($assessments as $ast) {
            $ast->total_questions = DB::table('academy_questions')->where('assessment_id', $ast->id)->count();
            $ast->questions = DB::table('academy_questions')->where('assessment_id', $ast->id)->get();
            $ast->targets = DB::table('academy_targets')->where('assessment_id', $ast->id)->pluck('jabatan')->toArray();
        }

        // Monitoring Tab Data
        $results = DB::table('academy_results as r')
            ->join('academy_assessments as a', 'r.assessment_id', '=', 'a.id')
            ->select('r.*', 'a.title as assessment_title')
            ->orderBy('r.id', 'desc')
            ->get();

        // Jabatan Options from Employees
        $jabatanList = DB::table('employees')
            ->whereNotNull('jabatan_roster')
            ->where('jabatan_roster', '!=', '')
            ->distinct()
            ->pluck('jabatan_roster')
            ->sort()
            ->values();

        return Inertia::render('OneAcademy/EvaluasiKompetensi/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_academy_menu'),
            'activeTab' => $activeTab,
            'search' => $search,
            'materiList' => $materiList,
            'assessments' => $assessments,
            'results' => $results,
            'jabatanList' => $jabatanList,
        ]);
    }

    public function storeMateri(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'category' => 'nullable|string',
            'revision' => 'nullable|string',
            'description' => 'nullable|string',
            'risk_level' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:active,revision_needed,expired',
            'effective_date' => 'nullable|date',
            'expired_date' => 'nullable|date',
        ]);

        $user = $this->currentUser();
        $validated['created_by'] = $user['name'] ?? 'Admin';
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        DB::table('academy_materi')->insert($validated);

        return back()->with('success', 'Materi Training berhasil ditambahkan.');
    }

    public function storeAssessment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'procedure_id' => 'nullable|exists:academy_materi,id',
            'passing_grade' => 'required|integer|min:0|max:100',
            'timer_minutes' => 'required|integer|min:1',
            'retry_limit' => 'required|integer|min:1',
            'validity_months' => 'required|integer|min:0',
        ]);

        $validated['status'] = 'draft';
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        DB::table('academy_assessments')->insert($validated);

        return back()->with('success', 'Assessment berhasil dibuat.');
    }

    public function storeQuestion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'assessment_id' => 'required|exists:academy_assessments,id',
            'question_type' => 'required|in:multiple_choice,true_false,case_based',
            'question' => 'required|string',
            'option_a' => 'nullable|string',
            'option_b' => 'nullable|string',
            'option_c' => 'nullable|string',
            'option_d' => 'nullable|string',
            'correct' => 'nullable|string',
            'explanation' => 'nullable|string',
        ]);

        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        DB::table('academy_questions')->insert($validated);

        return back()->with('success', 'Soal berhasil ditambahkan ke Bank Soal.');
    }

    public function saveTargets(Request $request): RedirectResponse
    {
        $assessmentId = $request->input('assessment_id');
        $targets = $request->input('targets', []);

        DB::table('academy_targets')->where('assessment_id', $assessmentId)->delete();

        foreach ($targets as $jabatan) {
            DB::table('academy_targets')->insert([
                'assessment_id' => $assessmentId,
                'jabatan' => $jabatan,
                'created_at' => now(),
            ]);
        }

        return back()->with('success', 'Target distribusi berhasil diperbarui.');
    }
}
