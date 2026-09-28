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
        'randomize_questions',
        'mandatory_read',
        'status',
        'target_roles',
        'signed_by',
        'signer_signature',
        'signer_signed_at',
        'created_by',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'passing_grade' => 'integer',
        'timer_minutes' => 'integer',
        'retry_limit' => 'integer',
        'validity_months' => 'integer',
        'randomize_questions' => 'boolean',
        'mandatory_read' => 'boolean',
        'signer_signed_at' => 'datetime',
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

    public function certificates()
    {
        return $this->hasMany(ProcedureCertificate::class, 'assessment_id');
    }

    public function signer()
    {
        return $this->belongsTo(Employee::class, 'signed_by');
    }
}
