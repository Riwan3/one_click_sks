<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MppUnitPhoto extends Model
{
    use SoftDeletes;

    protected $table = 'mpp_unit_photos';
    protected $fillable = [
        'jenis', 'unit_code', 'tanggal', 'shift', 'nrp',
        'nama', 'url', 'watermark_data', 'created_by'
    ];
}
