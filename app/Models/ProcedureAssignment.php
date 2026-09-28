<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedureAssignment extends Model
{
    protected $fillable = [
        'assessment_id',
        'nrp',
    ];

    public function assessment()
    {
        return $this->belongsTo(ProcedureAssessment::class, 'assessment_id');
    }
}
