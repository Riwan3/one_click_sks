<?php

namespace App\Services;

use App\Models\AcademyAssessment;
use App\Models\AcademyNotification;
use App\Models\AcademyResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Monitoring Evaluasi Kompetensi - padanan AcademyMonitoringRepository di v1.
 * Satu baris = 1 karyawan x 1 assessment (dari academy_notifications) + hasil terakhirnya.
 */
class AcademyMonitoringService
{
    public const PER_PAGE = 20;

    private const JABATAN_SQL = "COALESCE(NULLIF(e.jabatan_ss6, ''), e.jabatan_roster)";

    public function normalizeFilters(array $input): array
    {
        $text = fn ($key) => trim((string) ($input[$key] ?? ''));

        return [
            'assessment_id' => (int) ($input['assessment_id'] ?? 0),
            'jabatan' => $text('jabatan'),
            'status' => $text('status'),
            'search' => $text('search'),
            'date_from' => $text('date_from'),
            'date_to' => $text('date_to'),
            'score_min' => $text('score_min'),
            'score_max' => $text('score_max'),
        ];
    }

    /** Tugaskan manual (dipakai saat input nilai manual utk karyawan yang belum di-assign). */
    public function assignEmployee(int $assessmentId, string $nrp): void
    {
        AcademyNotification::firstOrCreate(['assessment_id' => $assessmentId, 'nrp' => $nrp], ['sent_at' => now()]);
    }

    /**
     * Catat hasil evaluasi secara manual (mis. dinilai langsung oleh instruktur/GL
     * tanpa melalui sesi ujian mandiri). attempt = jumlah percobaan sebelumnya + 1.
     */
    public function recordManualResult(AcademyAssessment $assessment, string $nrp, float $score, ?int $timeTaken = null): AcademyResult
    {
        $this->assignEmployee($assessment->id, $nrp);

        $attempt = AcademyResult::where('assessment_id', $assessment->id)->where('nrp', $nrp)->max('attempt') + 1;

        return AcademyResult::create([
            'assessment_id' => $assessment->id,
            'nrp' => $nrp,
            'attempt' => $attempt,
            'score' => $score,
            'passed' => $score >= $assessment->passing_grade,
            'status' => 'completed',
            'completed_at' => now(),
            'time_taken' => $timeTaken,
        ]);
    }

    public function summary(int $assessmentId): array
    {
        $row = $this->baseQuery()
            ->when($assessmentId, fn ($q) => $q->where('an.assessment_id', $assessmentId))
            ->selectRaw('COUNT(*) AS total_assigned')
            ->selectRaw('SUM(la.id IS NOT NULL) AS completed')
            ->selectRaw("SUM(la.status = 'completed' AND la.passed = 1) AS passed")
            ->selectRaw("SUM(la.status = 'completed' AND la.passed = 0) AS failed")
            ->selectRaw('SUM(la.id IS NULL) AS pending')
            ->selectRaw('SUM(lt.attempts > 1) AS retest')
            ->first();

        return array_map('intval', (array) $row);
    }

    /** Jumlah penyelesaian per hari (30 hari terakhir) untuk grafik tren. */
    public function dailyCompletions(int $assessmentId): Collection
    {
        return AcademyResult::query()
            ->when($assessmentId, fn ($q) => $q->where('assessment_id', $assessmentId))
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(completed_at) AS tgl, COUNT(*) AS cnt')
            ->groupByRaw('DATE(completed_at)')
            ->orderBy('tgl')
            ->get()
            ->map(fn ($r) => ['tgl' => $r->tgl, 'cnt' => (int) $r->cnt]);
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->resultsQuery($filters)->paginate(self::PER_PAGE)->withQueryString();
    }

    public function allForExport(array $filters): Collection
    {
        return $this->resultsQuery($filters)->get();
    }

    public function assessmentOptions(): Collection
    {
        return AcademyAssessment::whereIn('status', ['published', 'archived'])->latest()->get(['id', 'title']);
    }

    public function jabatanOptions(): Collection
    {
        return DB::table('academy_notifications as an')
            ->join('employees as e', 'e.nrp', '=', 'an.nrp')
            ->selectRaw(self::JABATAN_SQL.' AS jabatan')
            ->distinct()->orderBy('jabatan')->pluck('jabatan')->filter()->values();
    }

    public function detail(AcademyResult $result): array
    {
        $result->loadMissing(['assessment.materi', 'employee']);

        $history = AcademyResult::where('assessment_id', $result->assessment_id)->where('nrp', $result->nrp)
            ->orderBy('attempt')->get(['id', 'attempt', 'score', 'passed', 'status', 'completed_at']);

        $answers = DB::table('academy_answers as ans')
            ->join('academy_questions as q', 'q.id', '=', 'ans.question_id')
            ->where('ans.result_id', $result->id)
            ->select('ans.*', 'q.question', 'q.option_a', 'q.option_b', 'q.option_c', 'q.option_d', 'q.correct as correct_answer', 'q.explanation', 'q.question_type')
            ->orderBy('ans.id')
            ->get();

        return [
            'result' => [
                'id' => $result->id,
                'assessment_id' => $result->assessment_id,
                'nrp' => $result->nrp,
                'name' => $result->employee?->name ?? 'NRP '.$result->nrp,
                'jabatan' => $result->employee?->jabatan_ss6 ?: $result->employee?->jabatan_roster,
                'assessment_title' => $result->assessment?->title,
                'procedure_title' => $result->assessment?->materi?->title,
                'passing_grade' => (int) ($result->assessment?->passing_grade ?? 75),
                'score' => $result->score,
                'passed' => (bool) $result->passed,
                'attempt' => $result->attempt,
                'status' => $result->status,
                'time_taken' => $result->time_taken,
                'completed_at' => $result->completed_at?->format('Y-m-d H:i'),
            ],
            'answers' => $answers,
            'history' => $history,
        ];
    }

    /** Keluarkan karyawan dari assessment beserta seluruh hasilnya. */
    public function removeAssignment(int $assessmentId, string $nrp): void
    {
        DB::transaction(function () use ($assessmentId, $nrp) {
            AcademyResult::where('assessment_id', $assessmentId)->where('nrp', $nrp)->delete();
            AcademyNotification::where('assessment_id', $assessmentId)->where('nrp', $nrp)->delete();
        });
    }

    private function baseQuery(): Builder
    {
        $latest = DB::table('academy_results')
            ->selectRaw('assessment_id, nrp, MAX(id) AS id, COUNT(*) AS attempts')
            ->groupBy('assessment_id', 'nrp');

        return DB::table('academy_notifications as an')
            ->join('academy_assessments as a', 'a.id', '=', 'an.assessment_id')
            ->leftJoin('employees as e', 'e.nrp', '=', 'an.nrp')
            ->leftJoinSub($latest, 'lt', fn ($j) => $j->on('lt.assessment_id', '=', 'an.assessment_id')->on('lt.nrp', '=', 'an.nrp'))
            ->leftJoin('academy_results as la', 'la.id', '=', 'lt.id')
            ->whereIn('a.status', ['published', 'archived']);
    }

    private function resultsQuery(array $filters): Builder
    {
        $query = $this->baseQuery()
            ->select([
                'an.assessment_id', 'an.nrp', 'a.title as assessment_title', 'a.passing_grade',
                'e.name', 'la.id as result_id', 'la.score', 'la.status as result_status', 'la.passed',
                'la.completed_at', 'lt.attempts as attempt',
            ])
            ->selectRaw(self::JABATAN_SQL.' AS jabatan')
            ->when($filters['assessment_id'], fn ($q, $id) => $q->where('an.assessment_id', $id))
            ->when($filters['jabatan'], fn ($q, $j) => $q->whereRaw(self::JABATAN_SQL.' = ?', [$j]))
            ->when($filters['date_from'], fn ($q, $d) => $q->whereDate('la.completed_at', '>=', $d))
            ->when($filters['date_to'], fn ($q, $d) => $q->whereDate('la.completed_at', '<=', $d))
            ->when($filters['score_min'] !== '', fn ($q) => $q->where('la.score', '>=', (float) $filters['score_min']))
            ->when($filters['score_max'] !== '', fn ($q) => $q->where('la.score', '<=', (float) $filters['score_max']))
            ->when($filters['search'], fn ($q, $s) => $q->where(fn ($w) => $w->where('an.nrp', 'like', "%{$s}%")->orWhere('e.name', 'like', "%{$s}%")))
            ->orderByDesc('la.completed_at')
            ->orderBy('e.name');

        $statusSql = match ($filters['status']) {
            'PENDING' => 'la.id IS NULL',
            'IN_PROGRESS' => "la.status = 'in_progress'",
            'PASS' => "la.status = 'completed' AND la.passed = 1",
            'FAILED' => "la.status = 'completed' AND la.passed = 0",
            default => null,
        };

        return $statusSql ? $query->whereRaw($statusSql) : $query;
    }
}
