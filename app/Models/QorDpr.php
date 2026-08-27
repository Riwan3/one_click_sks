<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QorDpr extends Model
{
    protected $table = 'qor_dpr';

    protected $fillable = [
        'employee_id',
        'role_op',
        'tanggal',
        'loader',
        'jarak',
        'kode_unit',
        'rit',
        'total_bcm',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jarak' => 'decimal:3',
            'rit' => 'decimal:2',
            'total_bcm' => 'decimal:2',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeQuartal(Builder $query, int $tahun, int $quartal): Builder
    {
        return $query->whereBetween('tanggal', QorService::quartalRange($tahun, $quartal));
    }
}
