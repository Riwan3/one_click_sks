<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QorAtrAttendance extends Model
{
    protected $table = 'qor_atr_attendance';

    protected $fillable = [
        'nrp',
        'nama',
        'posisi',
        'tanggal',
        'code',
        'dept',
        'company',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }
}
