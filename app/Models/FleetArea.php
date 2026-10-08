<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Area Setting Fleet harian (induk cluster). Nama area disalin sebagai teks ke fleet_clusters.area. */
class FleetArea extends Model
{
    protected $fillable = ['tanggal', 'shift', 'name', 'created_by'];

    protected function casts(): array
    {
        return ['shift' => 'integer'];
    }
}
