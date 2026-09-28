<?php

namespace App\Models;

use App\Enums\GmpFindingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Temuan GMP & tindak lanjutnya (tabel gmp_temuan, sama seperti v1). */
class GmpFinding extends Model
{
    use SoftDeletes;

    public const PRIORITIES = ['Critical', 'High', 'Medium', 'Low'];

    public const SOURCE_AUTO = 'Penilaian GMP';

    public const SOURCE_MANUAL = 'Temuan Manual';

    protected $table = 'gmp_temuan';

    protected $fillable = [
        'finding_code', 'source_type', 'assessment_id', 'assessment_detail_id', 'year', 'week', 'area_id', 'location_id',
        'aspect_id', 'checklist_id', 'standard_description', 'finding_description', 'recommendation', 'priority',
        'pic_user_id', 'observer', 'finding_date', 'due_date', 'progress_percentage', 'root_cause', 'action_plan',
        'actual_action', 'obstacle', 'started_at', 'completed_at', 'submitted_for_verification_at', 'verified_by',
        'verified_at', 'verification_result', 'verification_note', 'status', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'finding_date' => 'date',
        'due_date' => 'date',
        'started_at' => 'date',
        'completed_at' => 'date',
        'submitted_for_verification_at' => 'datetime',
        'verified_at' => 'datetime',
        'progress_percentage' => 'integer',
        'status' => GmpFindingStatus::class,
    ];

    public function evidences()
    {
        return $this->hasMany(GmpFindingEvidence::class, 'finding_id')->orderBy('id');
    }

    public function histories()
    {
        return $this->hasMany(GmpFindingHistory::class, 'finding_id')->orderByDesc('id');
    }

    public function pic()
    {
        return $this->belongsTo(Employee::class, 'pic_user_id', 'nrp');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereIn('status', GmpFindingStatus::overdueEligible())
            ->whereNotNull('due_date')->whereDate('due_date', '<', now()->toDateString());
    }

    /** Status aktif yang deadline-nya belum lewat (atau belum ada). */
    public function scopeNotOverdue(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q->whereNull('due_date')->orWhereDate('due_date', '>=', now()->toDateString()));
    }

    public function computedStatus(): string
    {
        $isOverdue = in_array($this->status->value, GmpFindingStatus::overdueEligible(), true) && $this->due_date?->lt(now()->startOfDay());

        return $isOverdue ? GmpFindingStatus::OVERDUE : $this->status->value;
    }
}
