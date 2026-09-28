<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademyAssessment extends Model
{
    protected $table = 'academy_assessments';

    protected $fillable = [
        'procedure_id', 'title', 'passing_grade', 'timer_minutes', 'retry_limit',
        'validity_months', 'randomize_questions', 'mandatory_read', 'target_roles',
        'signed_by', 'status', 'created_by', 'signer_signature', 'signer_signed_at',
    ];

    protected function casts(): array
    {
        return [
            'target_roles' => 'array',
            'randomize_questions' => 'boolean',
            'mandatory_read' => 'boolean',
            'signer_signed_at' => 'datetime',
        ];
    }

    public function materi()
    {
        return $this->belongsTo(AcademyMateri::class, 'procedure_id');
    }

    public function signer()
    {
        return $this->belongsTo(Employee::class, 'signed_by');
    }

    public function questions()
    {
        return $this->hasMany(AcademyQuestion::class, 'assessment_id');
    }

    public function notifications()
    {
        return $this->hasMany(AcademyNotification::class, 'assessment_id');
    }

    public function results()
    {
        return $this->hasMany(AcademyResult::class, 'assessment_id');
    }
}
