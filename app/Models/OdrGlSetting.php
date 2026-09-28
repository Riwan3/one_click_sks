<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdrGlSetting extends Model
{
    protected $table = 'odr_gl_settings';

    protected $fillable = [
        'tanggal',
        'shift',
        'area',
        'unit',
        'front_pic',
        'disposal_pic',
    ];
}
