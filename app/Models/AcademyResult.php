<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademyResult extends Model
{
    protected $table = 'academy_results';

    protected $fillable = [
        'assessment_id', 'nrp', 'attempt', 'score', 'passed',
        'status', 'started_at', 'completed_at', 'time_taken',
    ];

    protected function casts(): array
    {
        return [
            'passed' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'score' => 'float',
        ];
    }

    public function assessment()
    {
        return $this->belongsTo(AcademyAssessment::class, 'assessment_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'nrp', 'nrp');
    }

    public function answers()
    {
        return $this->hasMany(AcademyAnswer::class, 'result_id');
    }
}
