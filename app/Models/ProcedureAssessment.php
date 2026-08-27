<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedureAssessment extends Model
{
    use HasFactory;

    protected $table = 'procedure_assessments';

    protected $fillable = [
        'procedure_id',
        'title',
        'passing_grade',
        'timer_minutes',
        'retry_limit',
        'validity_months',
        'status',
        'target_roles',
        'created_by',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'passing_grade' => 'integer',
        'timer_minutes' => 'integer',
        'retry_limit' => 'integer',
        'validity_months' => 'integer',
    ];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class, 'procedure_id');
    }

    public function questions()
    {
        return $this->hasMany(ProcedureQuestion::class, 'assessment_id');
    }

    public function evaluations()
    {
        return $this->hasMany(ProcedureEvaluation::class, 'assessment_id');
    }
}
