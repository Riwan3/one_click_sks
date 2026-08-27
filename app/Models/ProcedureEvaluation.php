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
        'score',
        'status',
        'completed_at',
        'certificate_code',
        'notes',
    ];

    protected $casts = [
        'score' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function assessment()
    {
        return $this->belongsTo(ProcedureAssessment::class, 'assessment_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'nrp', 'nrp');
    }
}
