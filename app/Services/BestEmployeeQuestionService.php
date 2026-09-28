<?php

namespace App\Services;

use App\Enums\BestEmployeeQuestionType;
use App\Models\BestEmployeeCampaign;
use App\Models\BestEmployeeQuestion;
use Illuminate\Support\Facades\DB;

class BestEmployeeQuestionService
{
    public function __construct(private BestEmployeeActivityLogger $logger) {}

    /**
     * Simpan soal (create/update) + full-replace opsi jawaban dalam satu transaction.
     * Hanya boleh dipanggil saat questionsLocked() bernilai false.
     */
    public function save(BestEmployeeCampaign $campaign, ?BestEmployeeQuestion $question, array $data, array $options, int $actorId): BestEmployeeQuestion
    {
        $type = BestEmployeeQuestionType::from($data['tipe_soal']);
        $duration = ! empty($data['use_default_duration']) ? null : ($data['duration_seconds'] ?? null);

        return DB::transaction(function () use ($campaign, $question, $data, $options, $actorId, $type, $duration) {
            $payload = [
                'campaign_id' => $campaign->id,
                'pertanyaan' => $data['pertanyaan'],
                'tipe_soal' => $type->value,
                'duration_seconds' => $duration,
                'bobot' => $data['bobot'],
                'is_required' => $data['is_required'] ?? true,
                'correct_answer' => $data['correct_answer'] ?? null,
                'updated_by' => $actorId,
            ];

            if ($question) {
                $question->update($payload);
            } else {
                $payload['urutan'] = $campaign->questions()->max('urutan') + 1;
                $payload['created_by'] = $actorId;
                $question = BestEmployeeQuestion::create($payload);
            }

            $question->options()->delete();
            if ($type === BestEmployeeQuestionType::MultipleChoice) {
                $order = 0;
                foreach ($options as $opt) {
                    $text = trim($opt['option_text'] ?? '');
                    if ($text === '') {
                        continue;
                    }
                    $order++;
                    $question->options()->create([
                        'option_text' => $text,
                        'option_order' => $order,
                        'is_correct' => ! empty($opt['is_correct']),
                    ]);
                }
            }

            $this->logger->log($campaign->id, $actorId, 'question_changed', ($question->wasRecentlyCreated ? 'Soal baru ditambahkan #' : 'Soal diubah #').$question->id);

            return $question->fresh('options');
        });
    }

    /**
     * Susun ulang urutan soal berdasarkan array id (urutan array = urutan baru).
     * Return false jika daftar id tidak persis sama dengan soal aktif kegiatan ini.
     */
    public function reorder(BestEmployeeCampaign $campaign, array $orderedQuestionIds, int $actorId): bool
    {
        $existingIds = $campaign->questions()->pluck('id')->sort()->values()->all();
        $ordered = collect($orderedQuestionIds)->map(fn ($v) => (int) $v)->unique()->values();

        if ($ordered->sort()->values()->all() !== $existingIds) {
            return false;
        }

        DB::transaction(function () use ($orderedQuestionIds, $campaign, $actorId) {
            foreach ($orderedQuestionIds as $i => $questionId) {
                BestEmployeeQuestion::where('id', $questionId)
                    ->where('campaign_id', $campaign->id)
                    ->update(['urutan' => $i + 1, 'updated_by' => $actorId]);
            }
        });

        $this->logger->log($campaign->id, $actorId, 'question_changed', 'Urutan soal diperbarui');

        return true;
    }

    public function delete(BestEmployeeQuestion $question, int $actorId): void
    {
        $question->update(['updated_by' => $actorId]);
        $question->delete();
        $this->logger->log($question->campaign_id, $actorId, 'question_changed', "Soal #{$question->id} dihapus");
    }
}
