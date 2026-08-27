<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetAllocation extends Model
{
    use HasFactory;

    protected $table = 'fleet_allocations';

    protected $fillable = [
        'tanggal',
        'shift',
        'group_name',
        'loader_id',
        'area',
        'front',
        'refueling_time',
        'unit_nrp',
        'unit_status',
        'distance',
    ];

    protected $casts = [
        'shift' => 'integer',
        'distance' => 'integer',
    ];
}
