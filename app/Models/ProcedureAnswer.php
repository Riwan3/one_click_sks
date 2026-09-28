<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedureAnswer extends Model
{
    protected $table = 'procedure_answers';

    protected $fillable = [
        'evaluation_id',
        'question_id',
        'user_answer',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function evaluation()
    {
        return $this->belongsTo(ProcedureEvaluation::class, 'evaluation_id');
    }

    public function question()
    {
        return $this->belongsTo(ProcedureQuestion::class, 'question_id');
    }
}
