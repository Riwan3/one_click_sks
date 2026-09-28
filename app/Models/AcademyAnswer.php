<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademyAnswer extends Model
{
    public $timestamps = false;

    protected $table = 'academy_answers';

    protected $fillable = ['result_id', 'question_id', 'user_answer', 'is_correct'];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }

    public function result()
    {
        return $this->belongsTo(AcademyResult::class, 'result_id');
    }

    public function question()
    {
        return $this->belongsTo(AcademyQuestion::class, 'question_id');
    }
}
