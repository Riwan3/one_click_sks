<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HmData extends Model
{
    protected $table = 'hm_data';

    protected $fillable = [
        'tanggal',
        'shift',
        'employee_id',
        'unit',
        'dept',
        'hm_start',
        'hm_stop',
        'hm_diff',
        'activity',
        'parking',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'hm_start' => 'decimal:2',
            'hm_stop' => 'decimal:2',
            'hm_diff' => 'decimal:2',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->whereHas(
            'employee',
            fn (Builder $q) => $q->where('nrp', 'like', "%{$keyword}%")
                ->orWhere('name', 'like', "%{$keyword}%")
        )->orWhere('unit', 'like', "%{$keyword}%");
    }

    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->where('tanggal', '>=', $from))
            ->when($to, fn (Builder $q) => $q->where('tanggal', '<=', $to));
    }

    public function scopeForEmployee(Builder $query, ?int $employeeId): Builder
    {
        return $employeeId ? $query->where('employee_id', $employeeId) : $query;
    }
}
