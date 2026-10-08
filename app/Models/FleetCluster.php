<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Cluster Setting Fleet harian: nama cluster sama dengan fleet_allocations.group_name; area = wilayah umum cluster. */
class FleetCluster extends Model
{
    protected $fillable = ['tanggal', 'shift', 'name', 'area', 'sort_order', 'created_by'];

    protected function casts(): array
    {
        return ['shift' => 'integer', 'sort_order' => 'integer'];
    }
}
