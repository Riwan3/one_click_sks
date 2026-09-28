<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BestEmployeeOption extends Model
{
    protected $table = 'training_be_options';

    protected $fillable = ['question_id', 'option_text', 'option_order', 'is_correct'];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }

    public function question()
    {
        return $this->belongsTo(BestEmployeeQuestion::class, 'question_id');
    }
}
