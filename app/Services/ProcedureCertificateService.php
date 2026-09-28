<?php

namespace App\Services;

use App\Enums\ProcedureCertificateStatus;
use App\Models\ProcedureAssessment;
use App\Models\ProcedureCertificate;
use App\Models\ProcedureEvaluation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Sertifikat kompetensi Evaluasi Prosedur (padanan EvaluasiCertificationRepository v1). */
class ProcedureCertificateService
{
    public const PER_PAGE = 20;

    private const EXPIRING_WINDOW_DAYS = 30;

    private const CODE_CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function normalizeFilters(array $input): array
    {
        $text = fn ($key) => trim((string) ($input[$key] ?? ''));

        return [
            'assessment_id' => (int) ($input['assessment_id'] ?? 0),
            'status' => $text('status'),
            'date_from' => $text('date_from'),
            'date_to' => $text('date_to'),
            'search' => $text('search'),
        ];
    }

    /** Sinkronkan status active / expiring_soon / expired berdasarkan valid_until (revoked tidak disentuh). */
    public function refreshStatuses(): void
    {
        $today = now()->toDateString();
        $expiringLimit = now()->addDays(self::EXPIRING_WINDOW_DAYS)->toDateString();
        $notRevoked = fn () => ProcedureCertificate::where('status', '!=', ProcedureCertificateStatus::Revoked);

        $notRevoked()->where('valid_until', '<', $today)->update(['status' => ProcedureCertificateStatus::Expired]);
        $notRevoked()->whereBetween('valid_until', [$today, $expiringLimit])->update(['status' => ProcedureCertificateStatus::ExpiringSoon]);
        $notRevoked()->where('valid_until', '>', $expiringLimit)->update(['status' => ProcedureCertificateStatus::Active]);
    }

    public function summary(): array
    {
        $counts = ProcedureCertificate::selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');

        return [
            'total' => (int) $counts->sum(),
            'active' => (int) ($counts['active'] ?? 0),
            'expiring_soon' => (int) ($counts['expiring_soon'] ?? 0),
            'expired' => (int) ($counts['expired'] ?? 0),
            'revoked' => (int) ($counts['revoked'] ?? 0),
        ];
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return ProcedureCertificate::query()
            ->with(['employee:id,nrp,name,jabatan_ss6,jabatan_roster', 'assessment:id,title,passing_grade,procedure_id', 'assessment.procedure:id,title'])
            ->when($filters['assessment_id'], fn ($q, $id) => $q->where('assessment_id', $id))
            ->when($filters['status'], fn ($q, $s) => $q->where('status', $s))
            ->when($filters['date_from'], fn ($q, $d) => $q->whereDate('issue_date', '>=', $d))
            ->when($filters['date_to'], fn ($q, $d) => $q->whereDate('issue_date', '<=', $d))
            ->when($filters['search'], fn ($q, $s) => $q->where(fn ($w) => $w->where('nrp', 'like', "%{$s}%")
                ->orWhereHas('employee', fn ($e) => $e->where('name', 'like', "%{$s}%"))))
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    /** Hasil lulus yang belum punya sertifikat aktif (untuk banner "Terbitkan Semua"). */
    public function passedWithoutCertificate(int $assessmentId): Builder
    {
        return ProcedureEvaluation::query()
            ->where('status', 'passed')
            ->when($assessmentId, fn ($q) => $q->where('assessment_id', $assessmentId))
            ->whereNotExists(fn ($q) => $q->from('procedure_certificates as pc')
                ->whereColumn('pc.assessment_id', 'procedure_evaluations.assessment_id')
                ->whereColumn('pc.nrp', 'procedure_evaluations.nrp')
                ->where('pc.status', '!=', ProcedureCertificateStatus::Revoked->value));
    }

    /** Terbitkan sertifikat untuk 1 hasil lulus; kembalikan sertifikat aktif yang sudah ada bila ada. */
    public function issue(ProcedureEvaluation $evaluation, string $issuedBy): ProcedureCertificate
    {
        $existing = ProcedureCertificate::where(['assessment_id' => $evaluation->assessment_id, 'nrp' => $evaluation->nrp])
            ->where('status', '!=', ProcedureCertificateStatus::Revoked)
            ->first();
        if ($existing) return $existing;

        $issueDate = now()->startOfDay();

        return ProcedureCertificate::create([
            'assessment_id' => $evaluation->assessment_id,
            'evaluation_id' => $evaluation->id,
            'nrp' => $evaluation->nrp,
            'certificate_code' => $this->generateCode(),
            'score' => $evaluation->score,
            'issue_date' => $issueDate,
            'valid_until' => $this->validUntil($issueDate, (int) $evaluation->assessment?->validity_months),
            'status' => ProcedureCertificateStatus::Active,
            'issued_by' => $issuedBy,
        ]);
    }

    /** Terbitkan untuk semua hasil lulus yang belum bersertifikat; kembalikan jumlah terbit. */
    public function issueAll(int $assessmentId, string $issuedBy): int
    {
        $evaluations = $this->passedWithoutCertificate($assessmentId)->with('assessment:id,validity_months')->orderBy('id')->get()
            ->unique(fn ($e) => $e->assessment_id.'|'.$e->nrp);

        DB::transaction(fn () => $evaluations->each(fn ($e) => $this->issue($e, $issuedBy)));

        return $evaluations->count();
    }

    public function revoke(ProcedureCertificate $certificate): void
    {
        $certificate->update(['status' => ProcedureCertificateStatus::Revoked]);
    }

    /** Hitung ulang masa berlaku sertifikat saat validity_months assessment diubah. */
    public function syncValidity(ProcedureAssessment $assessment): void
    {
        $assessment->certificates()->where('status', '!=', ProcedureCertificateStatus::Revoked)->get()
            ->each(fn (ProcedureCertificate $c) => $c->update([
                'valid_until' => $this->validUntil($c->issue_date, (int) $assessment->validity_months),
                'status' => ProcedureCertificateStatus::Active,
            ]));
    }

    public function expiringSoonCount(): int
    {
        return ProcedureCertificate::where('status', ProcedureCertificateStatus::ExpiringSoon)->count();
    }

    public function findByCode(string $code): ?ProcedureCertificate
    {
        return ProcedureCertificate::with(['employee', 'assessment.procedure', 'assessment.signer'])->where('certificate_code', $code)->first();
    }

    public function assessmentOptions(): Collection
    {
        return ProcedureAssessment::whereIn('status', ['published', 'archived'])->latest()->get(['id', 'title']);
    }

    private function validUntil(Carbon $issueDate, int $validityMonths): string
    {
        return $validityMonths > 0
            ? $issueDate->copy()->addMonths($validityMonths)->toDateString()
            : ProcedureCertificate::LIFETIME_DATE;
    }

    /** Format v1: EP-YYYYMM-XXXXXX (6 karakter tanpa huruf/angka ambigu). */
    private function generateCode(): string
    {
        do {
            $random = collect(range(1, 6))->map(fn () => self::CODE_CHARS[random_int(0, strlen(self::CODE_CHARS) - 1)])->implode('');
            $code = 'EP-'.now()->format('Ym').'-'.$random;
        } while (ProcedureCertificate::where('certificate_code', $code)->exists());

        return $code;
    }
}
