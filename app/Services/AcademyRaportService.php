<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/** Raport: matriks lulus/gagal per karyawan x per assessment (padanan bagian raport AcademyMonitoringRepository v1). */
class AcademyRaportService
{
    public function assessmentOptions()
    {
        return DB::table('academy_assessments')->where('status', 'published')->orderBy('created_at', 'desc')->get(['id', 'title']);
    }

    public function jabatanOptions()
    {
        return DB::table('academy_notifications as an')
            ->join('employees as e', 'e.nrp', '=', 'an.nrp')
            ->selectRaw("COALESCE(NULLIF(e.jabatan_ss6, ''), e.jabatan_roster) AS jabatan")
            ->distinct()->orderBy('jabatan')->pluck('jabatan')->filter()->values();
    }

    public function matrix(array $filters, string $search = ''): array
    {
        $employees = DB::table('academy_notifications as an')
            ->join('employees as e', 'e.nrp', '=', 'an.nrp')
            ->when($filters['jabatan'] ?? null, fn ($q, $j) => $q->whereRaw("COALESCE(NULLIF(e.jabatan_ss6, ''), e.jabatan_roster) = ?", [$j]))
            ->when($search, fn ($q, $s) => $q->where(fn ($w) => $w->where('e.nrp', 'like', "%{$s}%")->orWhere('e.name', 'like', "%{$s}%")))
            ->selectRaw("DISTINCT e.nrp, e.name, COALESCE(NULLIF(e.jabatan_ss6, ''), e.jabatan_roster) AS jabatan")
            ->orderBy('e.name')
            ->get();

        if ($employees->isEmpty()) {
            return [];
        }

        $nrps = $employees->pluck('nrp');

        $latest = DB::table('academy_results')
            ->selectRaw('assessment_id, nrp, MAX(id) AS id')
            ->whereIn('nrp', $nrps)
            ->groupBy('assessment_id', 'nrp');
        $resultsByEmpAssm = DB::table('academy_results as r')
            ->joinSub($latest, 'lt', fn ($j) => $j->on('lt.id', '=', 'r.id'))
            ->select('r.nrp', 'r.assessment_id', 'r.status', 'r.passed', 'r.score')
            ->get()
            ->groupBy('nrp');

        $assignedByEmp = DB::table('academy_notifications')->whereIn('nrp', $nrps)->get(['nrp', 'assessment_id'])->groupBy('nrp');

        return $employees->map(function ($emp) use ($assignedByEmp, $resultsByEmpAssm) {
            $assigned = ($assignedByEmp[$emp->nrp] ?? collect())->pluck('assessment_id');
            $resultsByAssm = ($resultsByEmpAssm[$emp->nrp] ?? collect())->keyBy('assessment_id');

            $passed = 0;
            $statuses = [];
            foreach ($assigned as $assessmentId) {
                $r = $resultsByAssm[$assessmentId] ?? null;
                if (! $r) {
                    $statuses[$assessmentId] = 'pending';
                } elseif ($r->status === 'in_progress') {
                    $statuses[$assessmentId] = 'in_progress';
                } elseif ($r->status === 'completed' && $r->passed) {
                    $statuses[$assessmentId] = 'passed';
                    $passed++;
                } else {
                    $statuses[$assessmentId] = 'failed';
                }
            }

            return [
                'employee' => $emp,
                'statuses' => $statuses,
                'total_assigned' => $assigned->count(),
                'total_passed' => $passed,
            ];
        })->values()->all();
    }

    public function employeeDetail(string $nrp): ?array
    {
        $employee = DB::table('employees')
            ->selectRaw("id, nrp, name, foto, COALESCE(NULLIF(jabatan_ss6, ''), jabatan_roster) AS jabatan, unit, lokasi_kerja AS lokasi")
            ->where('nrp', $nrp)->first();
        if (! $employee) {
            return null;
        }

        $latest = DB::table('academy_results')
            ->selectRaw('assessment_id, nrp, MAX(id) AS id')
            ->groupBy('assessment_id', 'nrp');

        $competencies = DB::table('academy_notifications as an')
            ->join('academy_assessments as aa', 'aa.id', '=', 'an.assessment_id')
            ->join('academy_materi as ap', 'ap.id', '=', 'aa.procedure_id')
            ->leftJoinSub($latest, 'lt', fn ($j) => $j->on('lt.assessment_id', '=', 'an.assessment_id')->on('lt.nrp', '=', 'an.nrp'))
            ->leftJoin('academy_results as r', 'r.id', '=', 'lt.id')
            ->where('an.nrp', $nrp)
            ->select('an.assessment_id', 'aa.title as assessment_title', 'ap.title as procedure_title', 'an.sent_at', 'r.status', 'r.passed', 'r.score', 'r.attempt', 'r.completed_at')
            ->orderByDesc('an.sent_at')
            ->get();

        return [
            'employee' => $employee,
            'competencies' => $competencies,
            'total_assigned' => $competencies->count(),
            'total_passed' => $competencies->where('status', 'completed')->where('passed', true)->count(),
        ];
    }
}
