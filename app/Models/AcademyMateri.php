<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AcademyMateri extends Model
{
    protected $table = 'academy_materi';

    protected $fillable = [
        'title', 'category', 'revision', 'description', 'risk_level', 'status',
        'effective_date', 'expired_date', 'file_path', 'file_type', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'expired_date' => 'date',
        ];
    }

    public function assessments()
    {
        return $this->hasMany(AcademyAssessment::class, 'procedure_id');
    }

    public function questions()
    {
        return $this->hasManyThrough(AcademyQuestion::class, AcademyAssessment::class, 'procedure_id', 'assessment_id');
    }

    public function notifications()
    {
        return $this->hasManyThrough(AcademyNotification::class, AcademyAssessment::class, 'procedure_id', 'assessment_id');
    }

    public function results()
    {
        return $this->hasManyThrough(AcademyResult::class, AcademyAssessment::class, 'procedure_id', 'assessment_id');
    }

    /** Soal / Assigned / Selesai per materi - dipakai kartu list Materi. */
    public function scopeWithEvaluationCounts(Builder $query): Builder
    {
        return $query->withCount([
            'questions as total_questions',
            'notifications as total_assigned',
            'results as total_completed' => fn ($q) => $q->where('academy_results.status', 'completed'),
        ]);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where(fn ($w) => $w->where('title', 'like', "%{$s}%")->orWhere('category', 'like', "%{$s}%")))
            ->when($filters['cat'] ?? null, fn ($q, $v) => $q->where('category', $v))
            ->when($filters['risk'] ?? null, fn ($q, $v) => $q->where('risk_level', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('effective_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('effective_date', '<=', $v));
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expired_date')->where('expired_date', '<', now()->toDateString())->where('status', '!=', 'expired');
    }
}
