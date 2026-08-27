<?php

namespace App\Http\Controllers;

use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BestEmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $id = (int) $request->input('id', 0);
        $tab = $request->string('tab', 'pengaturan')->toString();

        $campaigns = DB::table('training_be_campaigns as c')
            ->select('c.*')
            ->orderBy('c.id', 'desc')
            ->get();

        foreach ($campaigns as $camp) {
            $camp->total_questions = DB::table('training_be_questions')->where('campaign_id', $camp->id)->count();
            $camp->total_participants = DB::table('training_be_participants')->where('campaign_id', $camp->id)->count();
        }

        $activeCampaign = null;
        $questions = [];
        $participants = [];
        $attempts = [];

        if ($id > 0) {
            $activeCampaign = DB::table('training_be_campaigns')->where('id', $id)->first();
            if ($activeCampaign) {
                $questions = DB::table('training_be_questions')->where('campaign_id', $id)->orderBy('urutan', 'asc')->get();
                foreach ($questions as $q) {
                    $q->options = DB::table('training_be_options')->where('question_id', $q->id)->get();
                }

                $participants = DB::table('training_be_participants')->where('campaign_id', $id)->get();

                $attempts = DB::table('training_be_attempts as a')
                    ->join('training_be_participants as p', 'a.participant_id', '=', 'p.id')
                    ->where('a.campaign_id', $id)
                    ->select('a.*', 'p.user_nama', 'p.user_jabatan', 'p.user_unit')
                    ->orderBy('a.id', 'desc')
                    ->get();
            }
        }

        return Inertia::render('OneAcademy/BestEmployee/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_academy_menu'),
            'id' => $id,
            'tab' => $tab,
            'campaigns' => $campaigns,
            'activeCampaign' => $activeCampaign,
            'questions' => $questions,
            'participants' => $participants,
            'attempts' => $attempts,
        ]);
    }

    public function storeCampaign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'aturan' => 'nullable|string',
            'publish_start' => 'nullable|date',
            'publish_end' => 'nullable|date',
        ]);

        $validated['status'] = 'draft';
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        $campaignId = DB::table('training_be_campaigns')->insertGetId($validated);

        return redirect()->route('one-academy.best-employee.index', ['id' => $campaignId, 'tab' => 'pengaturan'])
            ->with('success', 'Kegiatan Best Employee berhasil dibuat.');
    }

    public function storeQuestion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campaign_id' => 'required|exists:training_be_campaigns,id',
            'tipe_soal' => 'required|in:multiple_choice,essay',
            'pertanyaan' => 'required|string',
            'bobot' => 'required|numeric|min:0.1',
            'options' => 'nullable|array',
            'options.*.text' => 'nullable|string',
            'options.*.is_correct' => 'nullable|boolean',
        ]);

        $campaignId = $validated['campaign_id'];
        $options = $validated['options'] ?? [];

        $urutan = DB::table('training_be_questions')->where('campaign_id', $campaignId)->count() + 1;

        $questionId = DB::table('training_be_questions')->insertGetId([
            'campaign_id' => $campaignId,
            'tipe_soal' => $validated['tipe_soal'],
            'pertanyaan' => $validated['pertanyaan'],
            'bobot' => $validated['bobot'],
            'urutan' => $urutan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($validated['tipe_soal'] === 'multiple_choice' && !empty($options)) {
            foreach ($options as $opt) {
                if (!empty($opt['text'])) {
                    DB::table('training_be_options')->insert([
                        'question_id' => $questionId,
                        'option_text' => $opt['text'],
                        'is_correct' => !empty($opt['is_correct']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        return back()->with('success', 'Soal berhasil ditambahkan.');
    }

    public function storeParticipant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campaign_id' => 'required|exists:training_be_campaigns,id',
            'user_nrp' => 'required|string',
            'user_nama' => 'required|string',
            'user_jabatan' => 'nullable|string',
            'user_unit' => 'nullable|string',
        ]);

        $validated['status'] = 'eligible';
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        DB::table('training_be_participants')->insert($validated);

        return back()->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function resetAttempt(Request $request): RedirectResponse
    {
        $attemptId = $request->input('attempt_id');

        DB::table('training_be_attempts')->where('id', $attemptId)->update([
            'status' => 'reset',
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Attempt peserta berhasil direset.');
    }
}
