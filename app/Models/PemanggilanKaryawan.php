<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PemanggilanKaryawan extends Model
{
    protected $fillable = [
        'employee_id',
        'nrp',
        'nama',
        'jabatan',
        'alasan_pemanggilan',
        'tempat',
        'jam_pemanggilan',
        'tanggal_pemanggilan',
        'nama_manpower',
        'fatigue_periode',
        'ttd_pemanggil',
        'ttd_karyawan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pemanggilan' => 'date',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
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

        return $query->where(
            fn (Builder $q) => $q->where('nrp', 'like', "%{$keyword}%")
                ->orWhere('nama', 'like', "%{$keyword}%")
        );
    }

    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('tanggal_pemanggilan', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('tanggal_pemanggilan', '<=', $to));
    }
}
