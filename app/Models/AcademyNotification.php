<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademyNotification extends Model
{
    public $timestamps = false;

    protected $table = 'academy_notifications';

    protected $fillable = ['assessment_id', 'nrp', 'is_read', 'sent_at'];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'sent_at' => 'datetime',
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
}
