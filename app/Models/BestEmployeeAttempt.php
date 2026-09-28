<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BestEmployeeAttempt extends Model
{
    protected $table = 'training_be_attempts';

    protected $fillable = [
        'campaign_id', 'participant_id', 'user_id', 'user_nrp', 'attempt_number', 'status',
        'started_at', 'submitted_at', 'total_score', 'objective_score', 'manual_score',
        'expired_reason', 'reset_reason', 'reset_by', 'reset_at', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'reset_at' => 'datetime',
            'total_score' => 'float',
            'objective_score' => 'float',
            'manual_score' => 'float',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(BestEmployeeCampaign::class, 'campaign_id');
    }

    public function participant()
    {
        return $this->belongsTo(BestEmployeeParticipant::class, 'participant_id');
    }

    public function answers()
    {
        return $this->hasMany(BestEmployeeAnswer::class, 'attempt_id');
    }
}
