<?php

namespace App\Models;

use App\Enums\BestEmployeeCampaignStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BestEmployeeCampaign extends Model
{
    use SoftDeletes;

    protected $table = 'training_be_campaigns';

    protected $fillable = [
        'judul', 'deskripsi', 'aturan', 'publish_start', 'publish_end', 'latest_start_at',
        'timezone', 'default_question_duration_seconds', 'total_duration_minutes', 'attempt_limit',
        'shuffle_questions', 'shuffle_options', 'allow_previous_question', 'auto_submit',
        'show_score_after_submit', 'show_correct_answer',
        'status', 'published_by', 'published_at', 'created_by', 'updated_by',
    ];

    /** Field kritis (jadwal & perilaku) yang terkunci begitu kegiatan keluar dari draft. */
    public const CRITICAL_FIELDS = [
        'publish_start', 'publish_end', 'latest_start_at', 'timezone',
        'default_question_duration_seconds', 'total_duration_minutes', 'attempt_limit',
        'shuffle_questions', 'shuffle_options', 'allow_previous_question', 'auto_submit',
        'show_score_after_submit', 'show_correct_answer',
    ];

    protected function casts(): array
    {
        return [
            'publish_start' => 'datetime',
            'publish_end' => 'datetime',
            'latest_start_at' => 'datetime',
            'published_at' => 'datetime',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'allow_previous_question' => 'boolean',
            'auto_submit' => 'boolean',
            'show_score_after_submit' => 'boolean',
            'show_correct_answer' => 'boolean',
        ];
    }

    public function questions()
    {
        return $this->hasMany(BestEmployeeQuestion::class, 'campaign_id')->orderBy('urutan');
    }

    public function participants()
    {
        return $this->hasMany(BestEmployeeParticipant::class, 'campaign_id');
    }

    public function attempts()
    {
        return $this->hasMany(BestEmployeeAttempt::class, 'campaign_id');
    }

    /**
     * Status efektif berdasarkan waktu server: scheduled naik ke active / turun
     * ke finished mengikuti jendela waktu, tanpa perlu job terjadwal.
     */
    public function effectiveStatus(): BestEmployeeCampaignStatus
    {
        $status = BestEmployeeCampaignStatus::from($this->status);
        if (in_array($status, [BestEmployeeCampaignStatus::Draft, BestEmployeeCampaignStatus::Cancelled, BestEmployeeCampaignStatus::Finished], true)) {
            return $status;
        }

        $now = now();
        if ($this->publish_end && $now->gte($this->publish_end)) {
            return BestEmployeeCampaignStatus::Finished;
        }
        if ($status === BestEmployeeCampaignStatus::Scheduled && $this->publish_start && $now->gte($this->publish_start)) {
            return BestEmployeeCampaignStatus::Active;
        }
        if ($status === BestEmployeeCampaignStatus::Active && $this->publish_start && $now->lt($this->publish_start)) {
            return BestEmployeeCampaignStatus::Scheduled;
        }

        return $status;
    }

    /** Soal terkunci begitu kegiatan active/finished/cancelled - tidak ada override. */
    public function questionsLocked(): bool
    {
        return in_array($this->effectiveStatus(), [
            BestEmployeeCampaignStatus::Active,
            BestEmployeeCampaignStatus::Finished,
            BestEmployeeCampaignStatus::Cancelled,
        ], true);
    }

    /** Peserta terkunci begitu kegiatan sudah dimulai/berakhir; override butuh admin + alasan. */
    public function participantsLocked(): bool
    {
        return $this->questionsLocked();
    }

    /** Pengaturan kritis (jadwal & perilaku) terkunci begitu kegiatan keluar dari draft. */
    public function settingsCriticalLocked(): bool
    {
        return $this->effectiveStatus() !== BestEmployeeCampaignStatus::Draft;
    }
}
