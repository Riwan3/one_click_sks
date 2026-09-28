<?php

namespace App\Services;

use App\Enums\GmpFindingStatus;
use App\Models\Employee;
use App\Models\GmpFinding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Temuan & Tindak Lanjut GMP (padanan index.php?type=temuan_gmp + api/gmp-temuan.php v1).
 * Alur: draft (otomatis dari approval) → open (PIC ditetapkan) → on_progress → waiting_verification → closed / rework.
 */
class GmpFindingService
{
    private const LIST_LIMIT = 200;

    private const EVIDENCE_DIR = 'uploads/gmp/temuan';

    /** Nilai filter "Sumber" → source_type tersimpan. */
    private const SOURCE_FILTERS = ['Otomatis' => GmpFinding::SOURCE_AUTO, 'Manual' => GmpFinding::SOURCE_MANUAL];

    public function normalizeFilters(array $input): array
    {
        $keys = ['tahun', 'week', 'area', 'lokasi', 'aspek', 'prioritas', 'pic', 'status', 'sumber', 'deadline_start', 'deadline_end', 'search'];

        return collect($keys)->mapWithKeys(fn ($key) => [$key => trim((string) ($input[$key] ?? ''))])->all();
    }

    public function stats(): array
    {
        $open = GmpFinding::where('status', GmpFindingStatus::Open)->notOverdue()->count();
        $progress = GmpFinding::where('status', GmpFindingStatus::OnProgress)->notOverdue()->count();
        $counts = GmpFinding::selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        $total = (int) $counts->sum();
        $closed = (int) ($counts[GmpFindingStatus::Closed->value] ?? 0);

        return [
            'total' => $total,
            'open' => $open,
            'progress' => $progress,
            'waiting' => (int) ($counts[GmpFindingStatus::WaitingVerification->value] ?? 0),
            'overdue' => GmpFinding::overdue()->count(),
            'closed' => $closed,
            'rate' => $total > 0 ? round($closed / $total * 100, 1) : 0,
        ];
    }

    public function list(array $f): Collection
    {
        return GmpFinding::query()
            ->with('pic:nrp,name')
            ->when($f['tahun'], fn ($q, $v) => $q->where('year', $v))
            ->when($f['week'], fn ($q, $v) => $q->where('week', 'like', "%{$v}%"))
            ->when($f['area'], fn ($q, $v) => $q->where('area_id', $v))
            ->when($f['lokasi'], fn ($q, $v) => $q->where('location_id', 'like', "%{$v}%"))
            ->when($f['aspek'], fn ($q, $v) => $q->where('aspect_id', $v))
            ->when($f['prioritas'], fn ($q, $v) => $q->where('priority', $v))
            ->when($f['pic'], fn ($q, $v) => $q->where(fn ($w) => $w->where('pic_user_id', 'like', "%{$v}%")->orWhereHas('pic', fn ($e) => $e->where('name', 'like', "%{$v}%"))))
            ->when(self::SOURCE_FILTERS[$f['sumber']] ?? null, fn ($q, $v) => $q->where('source_type', $v))
            ->when($f['deadline_start'] && $f['deadline_end'], fn ($q) => $q->whereBetween('due_date', [$f['deadline_start'], $f['deadline_end']]))
            ->when($f['search'], fn ($q, $v) => $q->where(fn ($w) => $w->where('finding_code', 'like', "%{$v}%")->orWhere('finding_description', 'like', "%{$v}%")))
            ->when($f['status'], fn ($q, $status) => match (true) {
                $status === GmpFindingStatus::OVERDUE => $q->overdue(),
                in_array($status, GmpFindingStatus::overdueEligible(), true) => $q->where('status', $status)->notOverdue(),
                default => $q->where('status', $status),
            })
            ->orderByDesc('id')
            ->limit(self::LIST_LIMIT)
            ->get();
    }

    public function yearOptions(): Collection
    {
        return GmpFinding::whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year');
    }

    /** Karyawan aktif untuk combobox PIC. */
    public function employeeOptions(): Collection
    {
        return Employee::query()
            ->where(fn ($q) => $q->whereNull('status_karyawan')->orWhere('status_karyawan', 'not like', '%INACTIVE%'))
            ->orderBy('name')
            ->get(['nrp', 'name', 'jabatan_ss6'])
            ->map(fn ($e) => ['nrp' => $e->nrp, 'nama' => $e->name, 'jabatan' => $e->jabatan_ss6]);
    }

    /** [prioritas => hari] dari Master SLA GMP. */
    public function slaDays(): array
    {
        return DB::table('gmp_master_sla')->pluck('sla_hari', 'prioritas')->map(fn ($d) => (int) $d)->all();
    }

    /** Tetapkan PIC & prioritas; draft otomatis menjadi open. */
    public function assignPic(GmpFinding $finding, array $data, string $actor): void
    {
        $oldStatus = $finding->status->value;
        $newStatus = $finding->status === GmpFindingStatus::Draft ? GmpFindingStatus::Open : $finding->status;

        $finding->update([
            'pic_user_id' => $data['pic_nrp'],
            'priority' => $data['prioritas'],
            'due_date' => $data['deadline'] ?? $this->deadlineFor($data['prioritas']),
            'observer' => $data['observer'] ?? null,
            'recommendation' => $data['rekomendasi_awal'] ?? null,
            'status' => $newStatus,
            'updated_by' => $actor,
        ]);

        $isOpened = $oldStatus !== $newStatus->value;
        $this->log($finding, $isOpened ? 'assign_pic' : 'update_pic', $oldStatus, $isOpened
            ? "PIC Ditetapkan dan Temuan di-Open. Prioritas: {$data['prioritas']}"
            : 'Informasi PIC diperbarui', $actor);
    }

    /**
     * Simpan progress corrective action; submit verifikasi mensyaratkan tindakan aktual,
     * progress 100% dan minimal satu foto sesudah.
     */
    public function updateProgress(GmpFinding $finding, array $data, ?UploadedFile $progressPhoto, ?UploadedFile $afterPhoto, bool $isSubmit, string $actor): void
    {
        if ($isSubmit) {
            $hasAfter = $afterPhoto || $finding->evidences()->where('evidence_type', 'after')->exists();
            $errors = array_filter([
                'tindakan_aktual' => empty($data['tindakan_aktual']) ? 'Tindakan aktual harus diisi untuk submit verifikasi.' : null,
                'progress' => (int) $data['progress'] < 100 ? 'Progress harus 100% untuk verifikasi.' : null,
                'foto_sesudah' => $hasAfter ? null : 'Foto sesudah (Evidence After) wajib diunggah.',
            ]);
            if ($errors) throw ValidationException::withMessages($errors);
        }

        $oldStatus = $finding->status->value;
        $newStatus = match (true) {
            $isSubmit => GmpFindingStatus::WaitingVerification,
            (int) $data['progress'] > 0 && $finding->status === GmpFindingStatus::Open => GmpFindingStatus::OnProgress,
            default => $finding->status,
        };

        DB::transaction(function () use ($finding, $data, $progressPhoto, $afterPhoto, $isSubmit, $actor, $oldStatus, $newStatus) {
            $this->storeEvidence($finding, 'progress', $progressPhoto, $actor);
            $this->storeEvidence($finding, 'after', $afterPhoto, $actor);

            $finding->update([
                'root_cause' => $data['analisis_penyebab'] ?? null,
                'action_plan' => $data['rencana_tindakan'] ?? null,
                'actual_action' => $data['tindakan_aktual'] ?? null,
                'progress_percentage' => (int) $data['progress'],
                'started_at' => $data['tanggal_mulai'] ?? null,
                'completed_at' => $data['tanggal_selesai_aktual'] ?? null,
                'obstacle' => $data['kendala'] ?? null,
                'status' => $newStatus,
                'submitted_for_verification_at' => $isSubmit ? now() : $finding->submitted_for_verification_at,
                'updated_by' => $actor,
            ]);

            $isSubmit
                ? $this->log($finding, 'submit_verification', $oldStatus, 'Submit laporan perbaikan (100%)', $actor)
                : $this->log($finding, 'update_progress', $oldStatus, "Progress diperbarui menjadi {$data['progress']}%", $actor);
        });
    }

    /** Keputusan verifikator: closed atau rework (komentar wajib bila rework — divalidasi di Form Request). */
    public function verify(GmpFinding $finding, array $data, ?UploadedFile $photo, string $actor): void
    {
        $oldStatus = $finding->status->value;
        $newStatus = $data['hasil_verifikasi'] === 'closed' ? GmpFindingStatus::Closed : GmpFindingStatus::Rework;

        DB::transaction(function () use ($finding, $data, $photo, $actor, $oldStatus, $newStatus) {
            $this->storeEvidence($finding, 'verification', $photo, $actor);
            $finding->update([
                'verified_by' => $actor,
                'verified_at' => now(),
                'verification_result' => $data['hasil_verifikasi'],
                'verification_note' => $data['komentar_verifikator'] ?? null,
                'status' => $newStatus,
                'updated_by' => $actor,
            ]);
            $this->log($finding, 'verification', $oldStatus, ($data['komentar_verifikator'] ?? '') ?: 'Verifikator memproses hasil perbaikan.', $actor);
        });
    }

    public function createManual(array $data, UploadedFile $beforePhoto, string $actor): GmpFinding
    {
        return DB::transaction(function () use ($data, $beforePhoto, $actor) {
            $finding = GmpFinding::create([
                'finding_code' => $this->nextCode(),
                'source_type' => GmpFinding::SOURCE_MANUAL,
                'finding_date' => now()->toDateString(),
                'year' => $data['tahun'] ?? now()->year,
                'week' => $data['week'] ?? null,
                'area_id' => $data['area_id'],
                'location_id' => $data['location_id'] ?? null,
                'aspect_id' => $data['aspect_id'] ?? null,
                'checklist_id' => $data['checklist_id'] ?? null,
                'finding_description' => $data['finding_description'],
                'recommendation' => $data['recommendation'] ?? null,
                'priority' => $data['priority'],
                'pic_user_id' => $data['pic_user_id'],
                'due_date' => $data['due_date'],
                'status' => GmpFindingStatus::Open,
                'created_by' => $actor,
            ]);
            $this->storeEvidence($finding, 'before', $beforePhoto, $actor);
            $this->log($finding, 'create', null, 'Temuan manual ditambahkan secara langsung beserta PIC dan Deadline.', $actor);

            return $finding;
        });
    }

    /**
     * Draft temuan otomatis untuk setiap item penilaian bernilai 0 saat penilaian di-approve.
     * Foto item (bila ada) dipakai sebagai foto sebelum, fallback ke foto area.
     */
    public function createDraftsFromAssessment(object $form, string $actor): int
    {
        $details = DB::table('gmp_penilaian_form_detail')->where('form_id', $form->id)->where('score_raw', 0)->get();

        foreach ($details as $detail) {
            $finding = GmpFinding::create([
                'finding_code' => $this->nextCode(),
                'source_type' => GmpFinding::SOURCE_AUTO,
                'assessment_id' => $form->id,
                'assessment_detail_id' => $detail->question_no,
                'year' => (int) Carbon::parse($form->tanggal_gmp)->year,
                'week' => $form->week,
                'area_id' => $form->tipe_gmp,
                'location_id' => $form->lokasi,
                'aspect_id' => strtoupper((string) $detail->section_key),
                'checklist_id' => $detail->criterion_name,
                'standard_description' => 'Belum sesuai standar',
                'finding_description' => 'Temuan dari Penilaian GMP - '.$detail->criterion_title,
                'finding_date' => $form->tanggal_gmp,
                'status' => GmpFindingStatus::Draft,
                'created_by' => $actor,
            ]);

            $photo = ($detail->foto_detail ?? null) ?: $form->foto_area;
            if ($photo) $finding->evidences()->create(['evidence_type' => 'before', 'file_path' => $photo, 'uploaded_by' => $actor]);
            $this->log($finding, 'create', null, 'Draft Temuan Dibuat Otomatis', $actor);
        }

        return $details->count();
    }

    public function deadlineFor(string $priority): string
    {
        return now()->addDays($this->slaDays()[$priority] ?? 7)->toDateString();
    }

    private function storeEvidence(GmpFinding $finding, string $type, ?UploadedFile $file, string $actor): void
    {
        if (! $file) return;

        $finding->evidences()->create([
            'evidence_type' => $type,
            'file_path' => '/storage/'.$file->store(self::EVIDENCE_DIR, 'public'),
            'original_name' => $file->getClientOriginalName(),
            'uploaded_by' => $actor,
        ]);
    }

    private function log(GmpFinding $finding, string $action, ?string $oldStatus, string $description, string $actor): void
    {
        $finding->histories()->create([
            'action_type' => $action,
            'old_status' => $oldStatus,
            'new_status' => $finding->status->value,
            'description' => $description,
            'created_by' => $actor,
        ]);
    }

    /** Format v1: GMP-FND-{tahun}-{id berikutnya 4 digit}. */
    private function nextCode(): string
    {
        $nextId = (int) GmpFinding::withTrashed()->max('id') + 1;

        return sprintf('GMP-FND-%s-%04d', now()->year, $nextId);
    }
}
