<?php

namespace App\Http\Controllers;

use App\Models\BestEmployeeAnswer;
use App\Models\BestEmployeeAttempt;
use App\Models\BestEmployeeCampaign;
use App\Services\BestEmployeeResultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BestEmployeeResultController extends Controller
{
    public function __construct(private BestEmployeeResultService $results) {}

    public function show(BestEmployeeAttempt $attempt): JsonResponse
    {
        abort_unless(Auth::user()->canViewBestEmployeeResults(), 403);

        return response()->json($this->results->attemptDetail($attempt));
    }

    public function reset(Request $request, BestEmployeeAttempt $attempt): RedirectResponse
    {
        abort_unless(Auth::user()->canManageBestEmployee(), 403);

        $request->validate(['reason' => ['required', 'string', 'max:500']], [
            'reason.required' => 'Alasan reset wajib diisi.',
        ]);

        $this->results->resetAttempt($attempt, Auth::id(), $request->string('reason')->toString());

        return back()->with('success', 'Attempt peserta berhasil direset.');
    }

    public function grade(Request $request, BestEmployeeAnswer $answer): RedirectResponse
    {
        abort_unless(Auth::user()->canManageBestEmployee(), 403);

        $request->validate([
            'score' => ['required', 'numeric', 'min:0'],
            'reviewer_note' => ['nullable', 'string', 'max:500'],
        ], ['score.required' => 'Nilai wajib diisi dengan angka.']);

        $result = $this->results->gradeManualAnswer($answer, (float) $request->input('score'), $request->input('reviewer_note'), Auth::id());
        if (isset($result['error'])) {
            return back()->withErrors(['score' => $result['error']]);
        }

        return back()->with('success', 'Nilai berhasil disimpan.');
    }

    public function export(Request $request, BestEmployeeCampaign $campaign): StreamedResponse
    {
        abort_unless(Auth::user()->canViewBestEmployeeResults(), 403);

        $rows = $this->results->ranking($campaign, $request->only(['q', 'jabatan']));
        $filename = 'hasil_'.preg_replace('/[^A-Za-z0-9_-]/', '_', $campaign->judul).'_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Ranking', 'NRP', 'Nama', 'Jabatan', 'Status', 'Waktu Mulai', 'Waktu Selesai', 'Nilai Objektif', 'Nilai Manual', 'Total Nilai', 'Persentase', 'Jawaban Benar', 'Timeout', 'Status Review']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->rank, $r->nrp, $r->nama, $r->jabatan,
                    $r->status === 'timed_out' ? 'Time Out' : 'Selesai',
                    $r->started_at, $r->submitted_at,
                    $r->objective_score, $r->manual_score, $r->total_score, $r->percentage.'%',
                    $r->correct_count, $r->timeout_count, $r->review_status,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=utf-8']);
    }
}
