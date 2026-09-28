<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    use HasFactory;

    protected $table = 'procedures';

    protected $fillable = [
        'title',
        'category',
        'revision',
        'description',
        'risk_level',
        'status',
        'file_path',
        'file_type',
        'effective_date',
        'expired_date',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expired_date' => 'date',
    ];

    public function assessments()
    {
        return $this->hasMany(ProcedureAssessment::class, 'procedure_id');
    }

    public function questions()
    {
        return $this->hasManyThrough(ProcedureQuestion::class, ProcedureAssessment::class, 'procedure_id', 'assessment_id');
    }

    public function assignments()
    {
        return $this->hasManyThrough(ProcedureAssignment::class, ProcedureAssessment::class, 'procedure_id', 'assessment_id');
    }

    /** Soal / Assigned / Selesai (karyawan yang sudah mengerjakan) per prosedur. */
    public function scopeWithEvaluationCounts(Builder $query): Builder
    {
        return $query->withCount([
            'questions as total_questions',
            'assignments as total_assigned',
            'assignments as total_completed' => fn ($q) => $q->whereExists(fn ($e) => $e->from('procedure_evaluations')
                ->whereColumn('procedure_evaluations.assessment_id', 'procedure_assignments.assessment_id')
                ->whereColumn('procedure_evaluations.nrp', 'procedure_assignments.nrp')),
        ]);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'], fn ($q, $s) => $q->where(fn ($w) => $w->where('title', 'like', "%{$s}%")->orWhere('category', 'like', "%{$s}%")))
            ->when($filters['cat'], fn ($q, $v) => $q->where('category', $v))
            ->when($filters['risk'], fn ($q, $v) => $q->where('risk_level', $v))
            ->when($filters['status'], fn ($q, $v) => $q->where('status', $v))
            ->when($filters['date_from'], fn ($q, $v) => $q->whereDate('effective_date', '>=', $v))
            ->when($filters['date_to'], fn ($q, $v) => $q->whereDate('effective_date', '<=', $v));
    }
}
