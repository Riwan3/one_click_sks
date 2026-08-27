<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['employee_id', 'tanggal', 'status', 'jam_masuk', 'jam_keluar', 'keterangan'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
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

        return $query->whereHas(
            'employee',
            fn (Builder $q) => $q->where('nrp', 'like', "%{$keyword}%")->orWhere('name', 'like', "%{$keyword}%")
        );
    }

    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->where('tanggal', '>=', $from))
            ->when($to, fn (Builder $q) => $q->where('tanggal', '<=', $to));
    }
}
