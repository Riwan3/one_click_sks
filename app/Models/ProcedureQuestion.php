<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedureQuestion extends Model
{
    use HasFactory;

    protected $table = 'procedure_questions';

    protected $fillable = [
        'assessment_id',
        'question_type',
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct',
        'explanation',
    ];

    public function assessment()
    {
        return $this->belongsTo(ProcedureAssessment::class, 'assessment_id');
    }
}
