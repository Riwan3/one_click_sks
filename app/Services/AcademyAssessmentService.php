<?php

namespace App\Services;

use App\Models\AcademyAssessment;
use App\Models\AcademyNotification;
use App\Models\AcademyQuestion;
use App\Models\Employee;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Assessment & bank soal Evaluasi Kompetensi (padanan AcademyAssessmentRepository v1). */
class AcademyAssessmentService
{
    /** Jabatan yang tidak ikut target distribusi saat fallback ke jabatan_roster. */
    private const EXCLUDED_ROSTER_ROLES = [
        'admin', 'TAMU', 'Testing', 'SHE', 'PJO', 'PENANGGUNG JAWAB OPERASIONAL', 'OPERATION INSTRUCTOR', 'OPERATION INSTRUCTUR',
    ];

    private const EXCLUDED_ROSTER_PATTERNS = ['%SECTION HEAD%', '%DEPT%HEAD%', 'ACT%HEAD%', '%MANAGER%', 'Roster GL%'];

    public function listAll(): Collection
    {
        return AcademyAssessment::with('materi:id,title')->withCount('questions')->latest()->get();
    }

    /** Pilihan target: unit MPP non-staff, fallback ke jabatan_roster karyawan aktif. */
    public function targetRoleOptions(): Collection
    {
        $units = Unit::where('category_type', 'nonStaff')->orderBy('sort_order')->orderBy('name')->pluck('name');
        if ($units->isNotEmpty()) {
            return $units;
        }

        return $this->activeEmployees()
            ->whereNotNull('jabatan_roster')->where('jabatan_roster', '!=', '')
            ->whereNotIn('jabatan_roster', self::EXCLUDED_ROSTER_ROLES)
            ->where(fn ($q) => collect(self::EXCLUDED_ROSTER_PATTERNS)->each(fn ($p) => $q->where('jabatan_roster', 'not like', $p)))
            ->distinct()->orderBy('jabatan_roster')->pluck('jabatan_roster');
    }

    /** Penandatangan sertifikat: Section Head / Dept Head / Manager / PJO aktif. */
    public function signerOptions(): Collection
    {
        $keywords = ['SECTION HEAD', 'DEPT HEAD', 'DEPARTMENT HEAD', 'MANAGER', 'PENANGGUNG JAWAB OPERASIONAL', 'PJO'];

        return $this->activeEmployees()
            ->where(fn ($q) => collect($keywords)->each(fn ($k) => $q->orWhere('jabatan_ss6', 'like', "%{$k}%")))
            ->orderBy('jabatan_ss6')->orderBy('name')
            ->get(['id', 'name', 'jabatan_ss6']);
    }

    public function estimatedEmployees(array $roles): int
    {
        return empty($roles) ? 0 : $this->matchingEmployees($roles)->count();
    }

    public function create(array $data, string $createdBy): AcademyAssessment
    {
        return AcademyAssessment::create([
            ...$this->settingAttributes($data),
            'procedure_id' => $data['procedure_id'],
            'target_roles' => array_values(array_filter($data['target_roles'] ?? [])),
            'status' => 'draft',
            'created_by' => $createdBy,
        ]);
    }

    public function updateSettings(AcademyAssessment $assessment, array $data): void
    {
        $assessment->update([...$this->settingAttributes($data), 'signed_by' => $data['signed_by'] ?? null]);
    }

    public function saveTargets(AcademyAssessment $assessment, array $roles): void
    {
        $assessment->update(['target_roles' => array_values(array_filter(array_map('trim', $roles)))]);
    }

    /** Publish & tugaskan karyawan sesuai target. */
    public function publish(AcademyAssessment $assessment): int
    {
        $assessment->update(['status' => 'published']);

        return $this->distribute($assessment);
    }

    /**
     * Reset & tugaskan ulang karyawan yang cocok dengan target jabatan.
     * Karyawan yang sudah punya hasil evaluasi tetap dipertahankan.
     */
    public function distribute(AcademyAssessment $assessment): int
    {
        $roles = $assessment->target_roles ?: [];
        $nrps = empty($roles) ? collect() : $this->matchingEmployees($roles)->pluck('nrp');

        DB::transaction(function () use ($assessment, $nrps) {
            AcademyNotification::where('assessment_id', $assessment->id)
                ->whereNotExists(fn ($q) => $q->from('academy_results')
                    ->whereColumn('academy_results.assessment_id', 'academy_notifications.assessment_id')
                    ->whereColumn('academy_results.nrp', 'academy_notifications.nrp'))
                ->delete();

            AcademyNotification::insertOrIgnore($nrps->map(fn ($nrp) => [
                'assessment_id' => $assessment->id, 'nrp' => $nrp, 'sent_at' => now(),
            ])->all());
        });

        return $nrps->count();
    }

    public function archive(AcademyAssessment $assessment): void
    {
        $assessment->update(['status' => 'archived']);
    }

    public function delete(AcademyAssessment $assessment): void
    {
        $assessment->delete();
    }

    public function saveQuestion(AcademyAssessment $assessment, array $data, ?AcademyQuestion $question = null): AcademyQuestion
    {
        $isTrueFalse = $data['question_type'] === 'true_false';
        $attributes = [
            'question_type' => $data['question_type'],
            'question' => trim($data['question']),
            'option_a' => $isTrueFalse ? 'Benar' : ($data['option_a'] ?? null),
            'option_b' => $isTrueFalse ? 'Salah' : ($data['option_b'] ?? null),
            'option_c' => $isTrueFalse ? null : ($data['option_c'] ?? null),
            'option_d' => $isTrueFalse ? null : ($data['option_d'] ?? null),
            'correct' => strtoupper($data['correct']),
            'explanation' => $data['explanation'] ?? null,
        ];

        if ($question) {
            $question->update($attributes);

            return $question;
        }

        return $assessment->questions()->create($attributes);
    }

    public function deleteQuestion(AcademyQuestion $question): void
    {
        $question->delete();
    }

    public function sign(AcademyAssessment $assessment, string $signature): void
    {
        $assessment->update(['signer_signature' => $signature, 'signer_signed_at' => now()]);
    }

    /** Karyawan aktif yang jabatannya cocok dengan pattern unit MPP (atau nama jabatan jika tanpa pattern). */
    private function matchingEmployees(array $roles): Builder
    {
        $patternsByRole = Unit::whereIn('name', $roles)->pluck('patterns', 'name');

        $likes = collect($roles)->flatMap(function ($role) use ($patternsByRole) {
            $patterns = collect(explode(',', (string) ($patternsByRole[$role] ?? '')))
                ->map(fn ($p) => trim($p))
                ->filter(fn ($p) => strlen($p) >= 2);

            return $patterns->isNotEmpty() ? $patterns : [trim($role)];
        })->unique();

        return $this->activeEmployees()->where(fn ($q) => $likes->each(
            fn ($like) => $q->orWhere('jabatan_ss6', 'like', "%{$like}%")->orWhere('jabatan_roster', 'like', "%{$like}%")
        ));
    }

    private function activeEmployees(): Builder
    {
        return Employee::query()->where(fn ($q) => $q->whereNull('status_karyawan')->orWhere('status_karyawan', 'not like', '%INACTIVE%'));
    }

    private function settingAttributes(array $data): array
    {
        return [
            'title' => trim($data['title']),
            'passing_grade' => (int) ($data['passing_grade'] ?? 75),
            'timer_minutes' => (int) ($data['timer_minutes'] ?? 60),
            'retry_limit' => (int) ($data['retry_limit'] ?? 3),
            'validity_months' => (int) ($data['validity_months'] ?? 6),
            'randomize_questions' => (bool) ($data['randomize_questions'] ?? false),
            'mandatory_read' => (bool) ($data['mandatory_read'] ?? false),
        ];
    }
}
