<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdrAssignment extends Model
{
    protected $table = 'odr_assignments';
    protected $fillable = [
        'tanggal', 'shift', 'area', 'unit', 'pengawas',
        'aktivitas', 'task_done', 'keterangan', 'status',
        'gmp', 'inspeksi', 'high_risk'
    ];
}
