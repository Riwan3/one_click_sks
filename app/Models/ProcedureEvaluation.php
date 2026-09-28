<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedureEvaluation extends Model
{
    use HasFactory;

    protected $table = 'procedure_evaluations';

    protected $fillable = [
        'assessment_id',
        'nrp',
        'attempt',
        'score',
        'status',
        'started_at',
        'completed_at',
        'time_taken',
        'certificate_code',
        'notes',
    ];

    protected $casts = [
        'attempt' => 'integer',
        'score' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'time_taken' => 'integer',
    ];

    public function assessment()
    {
        return $this->belongsTo(ProcedureAssessment::class, 'assessment_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'nrp', 'nrp');
    }

    public function answers()
    {
        return $this->hasMany(ProcedureAnswer::class, 'evaluation_id');
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isPassed(): bool
    {
        return $this->status === 'passed';
    }
}
