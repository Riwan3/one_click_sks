<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademyQuestion extends Model
{
    protected $table = 'academy_questions';

    protected $fillable = [
        'assessment_id', 'question_type', 'question',
        'option_a', 'option_b', 'option_c', 'option_d', 'correct', 'explanation',
    ];

    public function assessment()
    {
        return $this->belongsTo(AcademyAssessment::class, 'assessment_id');
    }
}
