<?php

namespace App\Services;

use App\Enums\BestEmployeeCampaignStatus;
use App\Models\BestEmployeeCampaign;
use Illuminate\Support\Facades\DB;

class BestEmployeeCampaignService
{
    public function __construct(private BestEmployeeActivityLogger $logger) {}

    public function create(array $data, int $actorId): BestEmployeeCampaign
    {
        $campaign = BestEmployeeCampaign::create([
            ...$data,
            'status' => BestEmployeeCampaignStatus::Draft->value,
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);

        $this->logger->log($campaign->id, $actorId, 'activity_created', 'Kegiatan dibuat: '.$campaign->judul);

        return $campaign;
    }

    /**
     * Update pengaturan kegiatan. $allowCritical=false membatasi ke judul/deskripsi/aturan
     * saja (dipakai saat kegiatan terkunci tanpa override force).
     */
    public function update(BestEmployeeCampaign $campaign, array $data, int $actorId, bool $allowCritical, ?string $overrideReason = null): void
    {
        $allowed = ['judul', 'deskripsi', 'aturan'];
        if ($allowCritical) {
            $allowed = [...$allowed, ...BestEmployeeCampaign::CRITICAL_FIELDS];
        }

        $campaign->fill([...array_intersect_key($data, array_flip($allowed)), 'updated_by' => $actorId]);
        $campaign->save();

        $description = $overrideReason ? 'Override pengaturan kritis: '.$overrideReason : 'Pengaturan kegiatan diubah';
        $this->logger->log($campaign->id, $actorId, 'activity_updated', $description, $overrideReason ? ['forced' => true, 'reason' => $overrideReason] : null);
    }

    public function stats(BestEmployeeCampaign $campaign): array
    {
        return [
            'total_questions' => $campaign->questions()->count(),
            'total_participants' => $campaign->participants()->assigned()->count(),
        ];
    }

    /** Checklist kesiapan publish. */
    public function publishReadiness(BestEmployeeCampaign $campaign): array
    {
        $questions = $campaign->questions()->with('options')->get();
        $participantCount = $campaign->participants()->assigned()->count();
        $now = now();

        $scheduleOk = $campaign->publish_start && $campaign->publish_end
            && $campaign->publish_start->lt($campaign->publish_end)
            && (! $campaign->latest_start_at || ($campaign->latest_start_at->gte($campaign->publish_start) && $campaign->latest_start_at->lte($campaign->publish_end)));

        $durationOk = true;
        $answerOk = true;
        $weightOk = true;
        $totalWeight = 0;
        foreach ($questions as $q) {
            $effectiveDuration = $q->duration_seconds ?? $campaign->default_question_duration_seconds;
            if (! $effectiveDuration || $effectiveDuration <= 0) {
                $durationOk = false;
            }
            if ((float) $q->bobot <= 0) {
                $weightOk = false;
            }
            $totalWeight += (float) $q->bobot;

            if ($q->isMultipleChoice()) {
                $hasCorrect = $q->options->contains('is_correct', true);
                if ($q->options->count() < 2 || ! $hasCorrect) {
                    $answerOk = false;
                }
            }
        }

        $items = [
            ['key' => 'judul', 'label' => 'Judul kegiatan', 'ok' => trim((string) $campaign->judul) !== '', 'detail' => trim((string) $campaign->judul) !== '' ? $campaign->judul : 'Belum diisi'],
            ['key' => 'jadwal', 'label' => 'Pengaturan jadwal', 'ok' => $scheduleOk, 'detail' => $scheduleOk ? 'Lengkap' : 'Waktu publish / selesai belum diisi atau tidak valid'],
            ['key' => 'instruksi', 'label' => 'Instruksi & aturan', 'ok' => trim((string) $campaign->aturan) !== '', 'detail' => trim((string) $campaign->aturan) !== '' ? 'Lengkap' : 'Belum diisi'],
            ['key' => 'soal', 'label' => 'Soal assessment', 'ok' => $questions->count() > 0, 'detail' => $questions->count().' soal'],
            ['key' => 'peserta', 'label' => 'Peserta', 'ok' => $participantCount > 0, 'detail' => $participantCount.' peserta'],
            ['key' => 'durasi_soal', 'label' => 'Durasi soal', 'ok' => $durationOk, 'detail' => $durationOk ? 'Lengkap' : 'Ada soal tanpa durasi efektif (khusus/default)'],
            ['key' => 'jawaban_benar', 'label' => 'Jawaban benar (multiple choice)', 'ok' => $answerOk, 'detail' => $answerOk ? 'Lengkap' : 'Ada soal pilihan ganda dengan opsi < 2 atau tanpa jawaban benar'],
            ['key' => 'bobot', 'label' => 'Total bobot soal', 'ok' => $weightOk && $totalWeight > 0, 'detail' => $weightOk ? "Total bobot $totalWeight (valid)" : 'Ada soal dengan bobot <= 0'],
            ['key' => 'waktu_selesai', 'label' => 'Waktu selesai belum terlewati', 'ok' => (bool) ($campaign->publish_end && $campaign->publish_end->gt($now)), 'detail' => ! $campaign->publish_end ? 'Waktu selesai belum diisi' : ($campaign->publish_end->gt($now) ? 'Lengkap' : 'Sudah terlewati')],
        ];

        return ['ready' => collect($items)->every(fn ($it) => $it['ok']), 'items' => $items];
    }

    public function publish(BestEmployeeCampaign $campaign, int $actorId): void
    {
        $now = now();
        $resultStatus = ($campaign->publish_start && $campaign->publish_start->lte($now))
            ? BestEmployeeCampaignStatus::Active
            : BestEmployeeCampaignStatus::Scheduled;

        $campaign->update([
            'status' => $resultStatus->value,
            'published_by' => $actorId,
            'published_at' => $now,
            'updated_by' => $actorId,
        ]);

        $this->logger->log($campaign->id, $actorId, 'assessment_published', 'Kegiatan dipublish, status: '.$resultStatus->value);
    }

    public function cancel(BestEmployeeCampaign $campaign, int $actorId, ?string $reason): void
    {
        $previousStatus = $campaign->status;
        $campaign->update(['status' => BestEmployeeCampaignStatus::Cancelled->value, 'updated_by' => $actorId]);

        $this->logger->log(
            $campaign->id,
            $actorId,
            'activity_updated',
            'Kegiatan dibatalkan'.($reason ? ": $reason" : ''),
            ['reason' => $reason, 'previous_status' => $previousStatus]
        );
    }

    public function softDelete(BestEmployeeCampaign $campaign, int $actorId): void
    {
        DB::transaction(function () use ($campaign, $actorId) {
            $campaign->update(['updated_by' => $actorId]);
            $campaign->delete();
        });

        $this->logger->log($campaign->id, $actorId, 'activity_updated', 'Kegiatan dihapus: '.$campaign->judul);
    }
}
