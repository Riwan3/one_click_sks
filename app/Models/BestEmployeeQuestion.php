<?php

namespace App\Models;

use App\Enums\BestEmployeeQuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BestEmployeeQuestion extends Model
{
    use SoftDeletes;

    protected $table = 'training_be_questions';

    protected $fillable = [
        'campaign_id', 'pertanyaan', 'tipe_soal', 'urutan', 'duration_seconds',
        'bobot', 'is_required', 'correct_answer', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'bobot' => 'float',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(BestEmployeeCampaign::class, 'campaign_id');
    }

    public function options()
    {
        return $this->hasMany(BestEmployeeOption::class, 'question_id')->orderBy('option_order');
    }

    public function isMultipleChoice(): bool
    {
        return $this->tipe_soal === BestEmployeeQuestionType::MultipleChoice->value;
    }
}
