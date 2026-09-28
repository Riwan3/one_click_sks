<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BestEmployeeParticipant extends Model
{
    protected $table = 'training_be_participants';

    protected $fillable = [
        'campaign_id', 'user_id', 'user_nrp', 'user_nama', 'user_jabatan', 'user_unit',
        'status', 'assigned_by', 'assigned_at', 'removed_by', 'removed_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'removed_at' => 'datetime',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(BestEmployeeCampaign::class, 'campaign_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attempts()
    {
        return $this->hasMany(BestEmployeeAttempt::class, 'participant_id');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }
}
