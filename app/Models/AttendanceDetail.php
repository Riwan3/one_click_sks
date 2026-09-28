<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AttendanceDetail extends Model
{
    protected $table = 'attendance_details';

    protected $fillable = [
        'nrp',
        'name',
        'position',
        'tanggal',
        'jam_in',
        'jam_out',
        'job',
        'dept',
        'company',
        'code',
        'sia_category',
        'device',
        'owner',
        'device_count',
        'device_id',
        'employee_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'device_count' => 'integer',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('nrp', 'like', "%{$keyword}%")
                ->orWhere('name', 'like', "%{$keyword}%")
                ->orWhere('position', 'like', "%{$keyword}%")
                ->orWhere('code', 'like', "%{$keyword}%")
        );
    }

    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->where('tanggal', '>=', $from))
            ->when($to, fn (Builder $q) => $q->where('tanggal', '<=', $to));
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        return $category ? $query->where('sia_category', strtoupper($category)) : $query;
    }
}
