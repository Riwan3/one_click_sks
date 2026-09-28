<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BestEmployeeActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'training_be_activity_logs';

    protected $fillable = [
        'campaign_id', 'attempt_id', 'user_id', 'action_type', 'description',
        'metadata', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
