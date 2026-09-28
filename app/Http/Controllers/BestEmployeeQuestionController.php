<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveBestEmployeeQuestionRequest;
use App\Models\BestEmployeeCampaign;
use App\Models\BestEmployeeQuestion;
use App\Services\BestEmployeeQuestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BestEmployeeQuestionController extends Controller
{
    public function __construct(private BestEmployeeQuestionService $questions) {}

    public function store(SaveBestEmployeeQuestionRequest $request, BestEmployeeCampaign $campaign): RedirectResponse
    {
        abort_if($campaign->questionsLocked(), 423, 'Soal tidak dapat diubah karena kegiatan sudah berjalan/selesai/dibatalkan.');

        $validated = $request->validated();
        $question = null;
        if (! empty($validated['question_id'])) {
            $question = $campaign->questions()->findOrFail($validated['question_id']);
        }

        $this->questions->save($campaign, $question, $validated, $validated['options'] ?? [], Auth::id());

        return back()->with('success', 'Soal berhasil disimpan.');
    }

    public function reorder(Request $request, BestEmployeeCampaign $campaign): RedirectResponse
    {
        abort_unless(Auth::user()->canManageBestEmployee(), 403);
        abort_if($campaign->questionsLocked(), 423, 'Urutan soal tidak dapat diubah karena kegiatan sudah berjalan/selesai/dibatalkan.');

        $request->validate(['question_ids' => ['required', 'array']]);

        if (! $this->questions->reorder($campaign, $request->input('question_ids'), Auth::id())) {
            return back()->withErrors(['question_ids' => 'Daftar soal tidak cocok dengan data saat ini. Muat ulang halaman.']);
        }

        return back()->with('success', 'Urutan soal diperbarui.');
    }

    public function destroy(BestEmployeeCampaign $campaign, BestEmployeeQuestion $question): RedirectResponse
    {
        abort_unless(Auth::user()->canManageBestEmployee(), 403);
        abort_unless($question->campaign_id === $campaign->id, 404);
        abort_if($campaign->questionsLocked(), 423, 'Soal tidak dapat dihapus karena kegiatan sudah berjalan/selesai/dibatalkan.');

        $this->questions->delete($question, Auth::id());

        return back()->with('success', 'Soal berhasil dihapus.');
    }
}
