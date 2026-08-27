<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobLog extends Model
{
    protected $table = 'job_logs';

    protected $fillable = [
        'job_name',
        'status',
        'executed_at',
        'completed_at',
        'message',
        'error_log',
    ];

    protected function casts(): array
    {
        return [
            'executed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
