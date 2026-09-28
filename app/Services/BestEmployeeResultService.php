<?php

namespace App\Services;

use App\Models\BestEmployeeAnswer;
use App\Models\BestEmployeeAttempt;
use App\Models\BestEmployeeCampaign;
use Illuminate\Support\Facades\DB;

class BestEmployeeResultService
{
    public function __construct(private BestEmployeeActivityLogger $logger) {}

    /** Sub-query "attempt terakhir per peserta" - dipakai konsisten di monitoring & ranking. */
    private function latestAttemptIdsSubquery()
    {
        return DB::table('training_be_attempts')
            ->select('participant_id', DB::raw('MAX(id) as latest_id'))
            ->groupBy('participant_id');
    }

    private function baseParticipantAttemptQuery(BestEmployeeCampaign $campaign)
    {
        return DB::table('training_be_participants as p')
            ->leftJoinSub($this->latestAttemptIdsSubquery(), 'la_ids', 'la_ids.participant_id', '=', 'p.id')
            ->leftJoin('training_be_attempts as la', 'la.id', '=', 'la_ids.latest_id')
            ->where('p.campaign_id', $campaign->id)
            ->where('p.status', 'assigned');
    }

    public function processExpiredParticipants(BestEmployeeCampaign $campaign): int
    {
        if (! $campaign->publish_end || now()->lt($campaign->publish_end)) {
            return 0;
        }

        $reason = 'Tidak memulai assessment sampai batas waktu berakhir.';
        $participants = $campaign->participants()->assigned()
            ->whereDoesntHave('attempts')
            ->get(['id', 'user_id', 'user_nrp']);

        foreach ($participants as $participant) {
            $attempt = BestEmployeeAttempt::create([
                'campaign_id' => $campaign->id,
                'participant_id' => $participant->id,
                'user_id' => $participant->user_id,
                'user_nrp' => $participant->user_nrp,
                'attempt_number' => 1,
                'status' => 'expired',
                'expired_reason' => $reason,
            ]);
            $this->logger->log($campaign->id, $participant->user_id, 'attempt_expired', $reason, null, $attempt->id);
        }

        return $participants->count();
    }

    public function monitoringSummary(BestEmployeeCampaign $campaign): array
    {
        $this->processExpiredParticipants($campaign);

        $row = $this->baseParticipantAttemptQuery($campaign)
            ->selectRaw("
                COUNT(*) as total_participants,
                SUM(CASE WHEN la.id IS NULL THEN 1 ELSE 0 END) as belum_mulai,
                SUM(CASE WHEN la.status = 'in_progress' THEN 1 ELSE 0 END) as sedang_mengerjakan,
                SUM(CASE WHEN la.status = 'submitted' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN la.status = 'expired' THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN la.status = 'timed_out' THEN 1 ELSE 0 END) as timeout,
                SUM(CASE WHEN la.status = 'reset' THEN 1 ELSE 0 END) as reset,
                AVG(CASE WHEN la.status IN ('submitted','timed_out') THEN la.total_score ELSE NULL END) as avg_score
            ")
            ->first();

        $total = (int) $row->total_participants;
        $selesai = (int) $row->selesai;

        return [
            'total_participants' => $total,
            'belum_mulai' => (int) $row->belum_mulai,
            'sedang_mengerjakan' => (int) $row->sedang_mengerjakan,
            'selesai' => $selesai,
            'expired' => (int) $row->expired,
            'timeout' => (int) $row->timeout,
            'reset' => (int) $row->reset,
            'avg_score' => $row->avg_score !== null ? round((float) $row->avg_score, 2) : null,
            'completion_rate' => $total > 0 ? round($selesai / $total * 100, 1) : 0,
        ];
    }

    public function participantFilterOptions(BestEmployeeCampaign $campaign): array
    {
        $base = $campaign->participants()->assigned();

        return [
            'jabatan' => (clone $base)->whereNotNull('user_jabatan')->where('user_jabatan', '<>', '')->distinct()->orderBy('user_jabatan')->pluck('user_jabatan'),
            'unit' => (clone $base)->whereNotNull('user_unit')->where('user_unit', '<>', '')->distinct()->orderBy('user_unit')->pluck('user_unit'),
        ];
    }

    public function monitoringList(BestEmployeeCampaign $campaign, array $filters, int $page, int $perPage = 20): array
    {
        $this->processExpiredParticipants($campaign);
        $totalQuestions = $campaign->questions()->count();

        $answerAgg = DB::table('training_be_answers')
            ->select('attempt_id', DB::raw('SUM(is_locked) as locked_count'), DB::raw('SUM(is_answered) as answered_count'), DB::raw('SUM(is_timed_out) as timeout_count'))
            ->groupBy('attempt_id');

        $query = $this->baseParticipantAttemptQuery($campaign)
            ->leftJoinSub($answerAgg, 'ans', 'ans.attempt_id', '=', 'la.id')
            ->when($filters['status'] ?? null, function ($q, $status) {
                $status === 'belum_mulai' ? $q->whereNull('la.id') : $q->where('la.status', $status);
            })
            ->when($filters['jabatan'] ?? null, fn ($q, $v) => $q->where('p.user_jabatan', $v))
            ->when($filters['unit'] ?? null, fn ($q, $v) => $q->where('p.user_unit', $v))
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where(fn ($sub) => $sub->where('p.user_nrp', 'like', "%{$v}%")->orWhere('p.user_nama', 'like', "%{$v}%")))
            ->when(($filters['min_score'] ?? '') !== '', fn ($q) => $q->where('la.total_score', '>=', (float) $filters['min_score']))
            ->when(($filters['max_score'] ?? '') !== '', fn ($q) => $q->where('la.total_score', '<=', (float) $filters['max_score']));

        $total = (clone $query)->count('p.id');
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));

        $rows = $query
            ->select([
                'p.id as participant_id', 'p.user_nrp as nrp', 'p.user_nama as nama', 'p.user_jabatan as jabatan', 'p.user_unit as unit',
                'la.id as attempt_id', 'la.status as attempt_status', 'la.attempt_number',
                'la.started_at', 'la.submitted_at', 'la.total_score', DB::raw('NULL as last_activity_at'),
                'ans.locked_count', 'ans.answered_count', 'ans.timeout_count',
            ])
            ->orderBy('p.user_nama')
            ->forPage($page, $perPage)
            ->get()
            ->map(function ($r) use ($totalQuestions) {
                $r->display_status = $r->attempt_status ?: 'belum_mulai';
                $locked = (int) ($r->locked_count ?? 0);
                $r->total_questions = $totalQuestions;
                $r->progress_percent = $totalQuestions > 0 ? round($locked / $totalQuestions * 100) : 0;
                $r->answered_count = (int) ($r->answered_count ?? 0);
                $r->timeout_count = (int) ($r->timeout_count ?? 0);

                return $r;
            });

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function totalPossibleWeight(BestEmployeeCampaign $campaign): float
    {
        return (float) $campaign->questions()->sum('bobot');
    }

    /**
     * Ranking: hanya attempt submitted/timed_out. Urutan: total_score DESC,
     * jumlah jawaban benar DESC, durasi ASC. Nilai identik = rank sama (1,1,3,...).
     */
    public function ranking(BestEmployeeCampaign $campaign, array $filters = [])
    {
        $this->processExpiredParticipants($campaign);

        $answerAgg = DB::table('training_be_answers as a')
            ->join('training_be_questions as q', 'q.id', '=', 'a.question_id')
            ->select(
                'a.attempt_id',
                DB::raw("SUM(CASE WHEN a.automatic_score IS NOT NULL AND a.automatic_score > 0 THEN 1 ELSE 0 END) as correct_count"),
                DB::raw('SUM(a.is_timed_out) as timeout_count'),
                DB::raw("SUM(CASE WHEN q.tipe_soal <> 'multiple_choice' AND a.is_answered = 1 AND a.manual_score IS NULL THEN 1 ELSE 0 END) as pending_review_count")
            )
            ->groupBy('a.attempt_id');

        $rows = $this->baseParticipantAttemptQuery($campaign)
            ->leftJoinSub($answerAgg, 'ans', 'ans.attempt_id', '=', 'la.id')
            ->whereIn('la.status', ['submitted', 'timed_out'])
            ->when($filters['jabatan'] ?? null, fn ($q, $v) => $q->where('p.user_jabatan', $v))
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where(fn ($sub) => $sub->where('p.user_nrp', 'like', "%{$v}%")->orWhere('p.user_nama', 'like', "%{$v}%")))
            ->select([
                'p.id as participant_id', 'p.user_nrp as nrp', 'p.user_nama as nama', 'p.user_jabatan as jabatan',
                'la.id as attempt_id', 'la.status', 'la.objective_score', 'la.manual_score', 'la.total_score',
                'la.started_at', 'la.submitted_at',
                DB::raw('TIMESTAMPDIFF(SECOND, la.started_at, la.submitted_at) as duration_seconds'),
                'ans.correct_count', 'ans.timeout_count', 'ans.pending_review_count',
            ])
            ->get();

        $totalWeight = $this->totalPossibleWeight($campaign);

        $sorted = $rows->sort(function ($a, $b) {
            if ((float) $a->total_score !== (float) $b->total_score) {
                return (float) $b->total_score <=> (float) $a->total_score;
            }
            if ((int) $a->correct_count !== (int) $b->correct_count) {
                return (int) $b->correct_count <=> (int) $a->correct_count;
            }

            return ($a->duration_seconds ?? PHP_INT_MAX) <=> ($b->duration_seconds ?? PHP_INT_MAX);
        })->values();

        $rank = 0;
        $prevKey = null;
        return $sorted->values()->map(function ($r, $i) use (&$rank, &$prevKey, $totalWeight) {
            $key = round((float) $r->total_score, 4).'|'.(int) $r->correct_count.'|'.($r->duration_seconds ?? 'null');
            if ($key !== $prevKey) {
                $rank = $i + 1;
                $prevKey = $key;
            }
            $r->rank = $rank;
            $r->percentage = $totalWeight > 0 ? round((float) $r->total_score / $totalWeight * 100, 1) : 0;
            $r->review_status = ((int) $r->pending_review_count > 0) ? 'Belum Direview' : 'Selesai Direview';

            return $r;
        });
    }

    public function attemptDetail(BestEmployeeAttempt $attempt): array
    {
        $participant = $attempt->participant;

        $answers = DB::table('training_be_attempts as la')
            ->join('training_be_questions as q', 'q.campaign_id', '=', 'la.campaign_id')
            ->leftJoin('training_be_answers as a', fn ($j) => $j->on('a.attempt_id', '=', 'la.id')->on('a.question_id', '=', 'q.id'))
            ->where('la.id', $attempt->id)
            ->whereNull('q.deleted_at')
            ->orderBy('q.urutan')
            ->select('a.*', 'q.id as question_id', 'q.pertanyaan', 'q.tipe_soal', 'q.bobot as score_weight', 'q.correct_answer')
            ->get()
            ->map(function ($row) {
                $row->options = $row->tipe_soal === 'multiple_choice'
                    ? DB::table('training_be_options')->where('question_id', $row->question_id)->orderBy('option_order')->get()
                    : collect();

                return $row;
            });

        return [
            'attempt' => $attempt,
            'participant' => $participant,
            'answers' => $answers,
            'timeline' => $this->logger->timeline($attempt->campaign_id, $attempt->id),
        ];
    }

    /** Beri/ubah nilai manual untuk satu jawaban short_text/essay. */
    public function gradeManualAnswer(BestEmployeeAnswer $answer, float $score, ?string $note, int $reviewerId): array
    {
        $answer->loadMissing('question');
        if ($answer->question->tipe_soal === 'multiple_choice') {
            return ['error' => 'Soal pilihan ganda dinilai otomatis, tidak dapat dinilai manual.'];
        }
        if ($score < 0 || $score > (float) $answer->question->bobot) {
            return ['error' => 'Nilai harus di antara 0 dan '.$answer->question->bobot.'.'];
        }

        $wasGraded = $answer->manual_score !== null;
        $previousScore = $answer->manual_score;

        $answer->update([
            'manual_score' => $score,
            'final_score' => $score,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'reviewer_note' => $note ?: null,
        ]);

        $scores = $this->recomputeAttemptScores($answer->attempt_id);

        $this->logger->log(
            $answer->attempt->campaign_id,
            $reviewerId,
            $wasGraded ? 'result_score_changed' : 'result_reviewed',
            ($wasGraded ? 'Nilai manual diubah' : 'Nilai manual diberikan')." untuk soal #{$answer->question_id}: $score",
            ['answer_id' => $answer->id, 'question_id' => $answer->question_id, 'score' => $score, 'previous_score' => $previousScore],
            $answer->attempt_id
        );

        return ['ok' => true, 'scores' => $scores];
    }

    public function recomputeAttemptScores(int $attemptId): array
    {
        $rows = DB::table('training_be_answers as a')
            ->join('training_be_questions as q', 'q.id', '=', 'a.question_id')
            ->where('a.attempt_id', $attemptId)
            ->whereNotNull('a.final_score')
            ->select('q.tipe_soal', 'a.final_score')
            ->get();

        $objective = (float) $rows->where('tipe_soal', 'multiple_choice')->sum('final_score');
        $manual = (float) $rows->where('tipe_soal', '<>', 'multiple_choice')->sum('final_score');

        $scores = ['objective_score' => $objective, 'manual_score' => $manual, 'total_score' => $objective + $manual];
        BestEmployeeAttempt::whereKey($attemptId)->update($scores);

        return $scores;
    }

    public function resetAttempt(BestEmployeeAttempt $attempt, int $actorId, string $reason): void
    {
        $attempt->update([
            'status' => 'reset',
            'reset_by' => $actorId,
            'reset_reason' => mb_substr($reason, 0, 255),
            'reset_at' => now(),
        ]);

        $this->logger->log($attempt->campaign_id, $actorId, 'attempt_reset', 'Reset attempt #'.$attempt->id, ['reason' => $reason], $attempt->id);
    }
}
