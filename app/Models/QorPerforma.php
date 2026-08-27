<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QorPerforma extends Model
{
    protected $table = 'qor_performa';

    protected $fillable = [
        'employee_id',
        'tahun',
        'quartal',
        'loader',
        'jarak',
        'kode_unit',
        'rit_total',
        'bcm_total',
        'ranking',
        'dari_operator',
    ];

    protected function casts(): array
    {
        return [
            'jarak' => 'decimal:3',
            'rit_total' => 'decimal:2',
            'bcm_total' => 'decimal:2',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopePeriod(Builder $query, int $tahun, int $quartal): Builder
    {
        return $query->where('tahun', $tahun)->where('quartal', $quartal);
    }
}
