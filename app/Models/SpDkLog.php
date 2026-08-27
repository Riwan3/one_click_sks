<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpDkLog extends Model
{
    protected $table = 'spdk_logs';

    protected $fillable = [
        'nrp',
        'action',
        'status',
        'response',
    ];

    public $timestamps = true;
}
