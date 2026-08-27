<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RosterEntry extends Model
{
    protected $fillable = [
        'employee_id',
        'tanggal',
        'kode',
        'unit',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeMonth(Builder $query, int $month, int $year): Builder
    {
        return $query->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
    }
}
