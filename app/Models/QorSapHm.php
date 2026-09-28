<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QorSapHm extends Model
{
    protected $table = 'qor_sap_hm';

    protected $fillable = [
        'measurement_doc',
        'nrp',
        'nama',
        'kode_unit',
        'alat_muat',
        'tanggal',
        'operation',
        'proses',
        'hour_time',
        'aktivitas',
        'hour_actv',
        'kode_unit_2',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'hour_time' => 'decimal:2',
            'hour_actv' => 'decimal:2',
        ];
    }
}
