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

    /** Jumlah unit fleet (loader + hauler) yang beroperasi pada tanggal itu, semua shift; unit Breakdown / Service tidak dihitung. */
    public static function operatingUnitCount(string $date): int
    {
        $rows = static::query()
            ->where('tanggal', $date)
            ->where(fn ($query) => $query->whereNull('group_name')->orWhere('group_name', '<>', 'BREAKDOWN / SERVICE'))
            ->get(['loader_id', 'unit_nrp']);

        return $rows->pluck('loader_id')->merge($rows->pluck('unit_nrp'))->filter()->unique()->count();
    }
}
