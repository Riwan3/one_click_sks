<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BestEmployeeAnswer extends Model
{
    protected $table = 'training_be_answers';

    protected $fillable = [
        'attempt_id', 'question_id', 'answer_text', 'selected_option_id',
        'is_answered', 'is_timed_out', 'is_locked', 'answered_at', 'time_spent_seconds',
        'automatic_score', 'manual_score', 'final_score', 'reviewed_by', 'reviewed_at', 'reviewer_note',
    ];

    protected function casts(): array
    {
        return [
            'is_answered' => 'boolean',
            'is_timed_out' => 'boolean',
            'is_locked' => 'boolean',
            'answered_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'automatic_score' => 'float',
            'manual_score' => 'float',
            'final_score' => 'float',
        ];
    }

    public function attempt()
    {
        return $this->belongsTo(BestEmployeeAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(BestEmployeeQuestion::class, 'question_id');
    }
}
