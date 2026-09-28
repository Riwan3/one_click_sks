<?php

namespace App\Services;

use App\Enums\ProcedureFollowupType;
use App\Models\Employee;
use App\Models\ProcedureAssessment;
use App\Models\ProcedureAssignment;
use App\Models\ProcedureEvaluation;
use App\Models\ProcedureFollowup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Monitoring Evaluasi Prosedur — padanan EvaluasiMonitoringRepository di v1.
 * Satu baris = 1 karyawan x 1 assessment (dari procedure_assignments) + hasil evaluasi terakhirnya.
 */
class ProcedureMonitoringService
{
    public const PER_PAGE = 20;

    private const JABATAN_SQL = "COALESCE(NULLIF(e.jabatan_ss6, ''), e.jabatan_roster)";

    private const COACHING_SQL = 'COALESCE(fu.pending_coaching_count, 0) > 0';

    private const REMEDIAL_SQL = 'COALESCE(fu.remedial_count, 0) > 0';

    /** Whitelist filter tabel monitoring, nilai kosong = tidak difilter. */
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

    /**
     * Sinkronkan flag lulus/tidak lulus dengan passing grade terbaru
     * (mis. passing grade diubah setelah hasil tersimpan).
     */
    public function recalcPassedFlags(): void
    {
        $passingGrade = '(SELECT a.passing_grade FROM procedure_assessments a WHERE a.id = procedure_evaluations.assessment_id)';

        ProcedureEvaluation::where('status', 'failed')->whereRaw("score >= {$passingGrade}")->update(['status' => 'passed']);
        ProcedureEvaluation::where('status', 'passed')->whereRaw("score < {$passingGrade}")->update(['status' => 'failed']);
    }

    public function assignEmployee(int $assessmentId, string $nrp): void
    {
        ProcedureAssignment::firstOrCreate(['assessment_id' => $assessmentId, 'nrp' => $nrp]);
    }

    public function summary(int $assessmentId): array
    {
        $row = $this->baseQuery()
            ->when($assessmentId, fn ($q) => $q->where('pa.assessment_id', $assessmentId))
            ->selectRaw('COUNT(*) AS total_assigned')
            ->selectRaw('SUM(pe.id IS NOT NULL) AS completed')
            ->selectRaw("SUM(pe.status = 'passed') AS passed")
            ->selectRaw("SUM(pe.status = 'failed') AS failed")
            ->selectRaw('SUM(pe.id IS NULL) AS pending')
            ->selectRaw('SUM(lt.attempts > 1) AS retest')
            ->selectRaw('SUM(NOT ('.self::COACHING_SQL.') AND '.self::REMEDIAL_SQL.') AS remedial')
            ->selectRaw('SUM(NOT ('.self::COACHING_SQL.') AND '.self::REMEDIAL_SQL." AND pe.status = 'passed') AS passed_after_remedial")
            ->selectRaw('SUM('.self::COACHING_SQL.') AS coaching_pending')
            ->first();

        return array_map('intval', (array) $row);
    }

    /** Jumlah penyelesaian per hari (30 hari terakhir) untuk grafik tren. */
    public function dailyCompletions(int $assessmentId): Collection
    {
        return ProcedureEvaluation::query()
            ->when($assessmentId, fn ($q) => $q->where('assessment_id', $assessmentId))
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
        return ProcedureAssessment::whereIn('status', ['published', 'archived'])
            ->latest()
            ->get(['id', 'title']);
    }

    public function jabatanOptions(): Collection
    {
        return DB::table('procedure_assignments as pa')
            ->join('employees as e', 'e.nrp', '=', 'pa.nrp')
            ->selectRaw(self::JABATAN_SQL.' AS jabatan')
            ->distinct()
            ->orderBy('jabatan')
            ->pluck('jabatan')
            ->filter()
            ->values();
    }

    /** Mentor coaching: Group Leader / Trainer aktif. */
    public function mentorOptions(): Collection
    {
        return Employee::query()
            ->where(fn ($q) => $q->where('jabatan_ss6', 'like', '%GROUP LEADER%')
                ->orWhere('jabatan_ss6', 'like', '%TRAINER%')
                ->orWhere('jabatan_ss6', 'like', '%GL %')
                ->orWhere('jabatan_ss6', 'GL'))
            ->where(fn ($q) => $q->whereNull('status_karyawan')->orWhere('status_karyawan', 'not like', '%INACTIVE%'))
            ->orderBy('name')
            ->get(['id', 'name', 'jabatan_ss6']);
    }

    public function detail(ProcedureEvaluation $evaluation): array
    {
        $evaluation->loadMissing(['assessment.procedure', 'employee']);
        $key = ['assessment_id' => $evaluation->assessment_id, 'nrp' => $evaluation->nrp];

        $history = ProcedureEvaluation::where($key)->where('status', '!=', 'in_progress')->orderBy('id')->get(['id', 'score', 'status', 'completed_at']);

        $followups = ProcedureFollowup::with('mentor:id,name')->where($key)->latest()->get();

        return [
            'result' => [
                'id' => $evaluation->id,
                'assessment_id' => $evaluation->assessment_id,
                'nrp' => $evaluation->nrp,
                'name' => $evaluation->employee?->name ?? 'NRP '.$evaluation->nrp,
                'jabatan' => $evaluation->employee?->jabatan_ss6 ?: $evaluation->employee?->jabatan_roster,
                'assessment_title' => $evaluation->assessment?->title,
                'procedure_title' => $evaluation->assessment?->procedure?->title,
                'passing_grade' => (int) ($evaluation->assessment?->passing_grade ?? 75),
                'score' => $evaluation->score,
                'passed' => $evaluation->status === 'passed',
                'attempt' => $history->search(fn ($h) => $h->id === $evaluation->id) + 1,
                'completed_at' => $evaluation->completed_at?->format('Y-m-d H:i'),
            ],
            'history' => $history->values()->map(fn ($h, $i) => [
                'attempt' => $i + 1,
                'score' => $h->score,
                'passed' => $h->status === 'passed',
            ]),
            'followups' => $followups->map(fn (ProcedureFollowup $f) => [
                'id' => $f->id,
                'type' => $f->type->value,
                'type_label' => $f->type->label(),
                'mentor_name' => $f->mentor?->name,
                'weak_material_notes' => $f->weak_material_notes,
                'coaching_target_date' => $f->coaching_target_date?->format('Y-m-d'),
                'remedial_granted_at' => $f->remedial_granted_at?->format('Y-m-d H:i'),
                'remedial_granted_by' => $f->remedial_granted_by,
            ]),
            'extra_attempts' => $followups->whereNotNull('remedial_granted_at')->count(),
        ];
    }

    public function grantDirectRemedial(ProcedureEvaluation $evaluation, string $grantedBy): ProcedureFollowup
    {
        return ProcedureFollowup::create([
            'assessment_id' => $evaluation->assessment_id,
            'nrp' => $evaluation->nrp,
            'evaluation_id' => $evaluation->id,
            'type' => ProcedureFollowupType::RemedialOnly,
            'remedial_granted_at' => now(),
            'remedial_granted_by' => $grantedBy,
            'created_by' => $grantedBy,
        ]);
    }

    public function scheduleCoaching(ProcedureEvaluation $evaluation, array $data, string $createdBy): ProcedureFollowup
    {
        return ProcedureFollowup::create([
            'assessment_id' => $evaluation->assessment_id,
            'nrp' => $evaluation->nrp,
            'evaluation_id' => $evaluation->id,
            'type' => ProcedureFollowupType::Coaching,
            'mentor_id' => $data['mentor_id'],
            'weak_material_notes' => $data['weak_material_notes'] ?? null,
            'coaching_target_date' => $data['coaching_target_date'],
            'created_by' => $createdBy,
        ]);
    }

    /** Coaching selesai → buka akses remedial. False jika sudah pernah diberikan. */
    public function grantFollowupRemedial(ProcedureFollowup $followup, string $grantedBy): bool
    {
        if ($followup->remedial_granted_at) return false;

        return $followup->update(['remedial_granted_at' => now(), 'remedial_granted_by' => $grantedBy]);
    }

    /** Keluarkan karyawan dari assessment beserta seluruh hasil & tindak lanjutnya. */
    public function removeAssignment(int $assessmentId, string $nrp): void
    {
        $key = ['assessment_id' => $assessmentId, 'nrp' => $nrp];

        DB::transaction(function () use ($key) {
            ProcedureFollowup::where($key)->delete();
            ProcedureEvaluation::where($key)->delete();
            ProcedureAssignment::where($key)->delete();
        });
    }

    private function baseQuery(): Builder
    {
        $latest = DB::table('procedure_evaluations')
            ->where('status', '!=', 'in_progress')
            ->selectRaw('assessment_id, nrp, MAX(id) AS id, COUNT(*) AS attempts')
            ->groupBy('assessment_id', 'nrp');

        $followups = DB::table('procedure_followups')
            ->selectRaw('assessment_id, nrp')
            ->selectRaw('SUM(remedial_granted_at IS NOT NULL) AS remedial_count')
            ->selectRaw("SUM(type = 'coaching' AND remedial_granted_at IS NULL) AS pending_coaching_count")
            ->groupBy('assessment_id', 'nrp');

        $sameKey = fn ($alias) => fn ($j) => $j->on("{$alias}.assessment_id", '=', 'pa.assessment_id')->on("{$alias}.nrp", '=', 'pa.nrp');

        return DB::table('procedure_assignments as pa')
            ->join('procedure_assessments as a', 'a.id', '=', 'pa.assessment_id')
            ->leftJoin('employees as e', 'e.nrp', '=', 'pa.nrp')
            ->leftJoinSub($latest, 'lt', $sameKey('lt'))
            ->leftJoin('procedure_evaluations as pe', 'pe.id', '=', 'lt.id')
            ->leftJoinSub($followups, 'fu', $sameKey('fu'))
            ->whereIn('a.status', ['published', 'archived']);
    }

    private function resultsQuery(array $filters): Builder
    {
        $query = $this->baseQuery()
            ->select([
                'pa.assessment_id', 'pa.nrp', 'a.title as assessment_title', 'a.passing_grade',
                'e.name', 'pe.id as evaluation_id', 'pe.score', 'pe.status as result_status',
                'pe.completed_at', 'lt.attempts as attempt',
            ])
            ->selectRaw(self::JABATAN_SQL.' AS jabatan')
            ->selectRaw('COALESCE(fu.remedial_count, 0) AS remedial_count')
            ->selectRaw('COALESCE(fu.pending_coaching_count, 0) AS pending_coaching_count')
            ->when($filters['assessment_id'], fn ($q, $id) => $q->where('pa.assessment_id', $id))
            ->when($filters['jabatan'], fn ($q, $j) => $q->whereRaw(self::JABATAN_SQL.' = ?', [$j]))
            ->when($filters['date_from'], fn ($q, $d) => $q->whereDate('pe.completed_at', '>=', $d))
            ->when($filters['date_to'], fn ($q, $d) => $q->whereDate('pe.completed_at', '<=', $d))
            ->when($filters['score_min'] !== '', fn ($q) => $q->where('pe.score', '>=', (float) $filters['score_min']))
            ->when($filters['score_max'] !== '', fn ($q) => $q->where('pe.score', '<=', (float) $filters['score_max']))
            ->when($filters['search'], fn ($q, $s) => $q->where(fn ($w) => $w->where('pa.nrp', 'like', "%{$s}%")->orWhere('e.name', 'like', "%{$s}%")))
            ->orderByDesc('pe.completed_at')
            ->orderBy('e.name');

        $statusSql = $this->statusSql($filters['status']);

        return $statusSql ? $query->whereRaw($statusSql) : $query;
    }

    private function statusSql(string $status): ?string
    {
        $notFollowedUp = 'NOT ('.self::COACHING_SQL.') AND NOT ('.self::REMEDIAL_SQL.')';

        return match ($status) {
            'PENDING' => 'pe.id IS NULL',
            'COACHING' => 'pe.id IS NOT NULL AND '.self::COACHING_SQL,
            'REMEDIAL' => 'pe.id IS NOT NULL AND NOT ('.self::COACHING_SQL.') AND '.self::REMEDIAL_SQL,
            'PASS' => "pe.status = 'passed' AND {$notFollowedUp}",
            'FAILED' => "pe.status = 'failed' AND {$notFollowedUp}",
            default => null,
        };
    }
}
