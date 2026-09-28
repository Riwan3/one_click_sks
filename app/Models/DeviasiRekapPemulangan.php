<?php

namespace App\Models;

use App\Models\Concerns\HasHashid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviasiRekapPemulangan extends Model
{
    use HasFactory, HasHashid;

    protected $table = 'deviasi_rekap_pemulangan';

    protected $fillable = [
        'nrp',
        'nama',
        'jabatan',
        'tanggal',
        'shift',
        'jam',
        'alasan',
        'keterangan',
        'konfirmasi',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date:Y-m-d',
        ];
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('nrp', 'like', "%{$keyword}%")
                ->orWhere('nama', 'like', "%{$keyword}%")
                ->orWhere('jabatan', 'like', "%{$keyword}%")
                ->orWhere('alasan', 'like', "%{$keyword}%")
                ->orWhere('keterangan', 'like', "%{$keyword}%")
                ->orWhere('konfirmasi', 'like', "%{$keyword}%");
        });
    }

    public function scopeDateRange(Builder $query, ?string $dateFrom, ?string $dateTo): Builder
    {
        if ($dateFrom) {
            $query->whereDate('tanggal', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('tanggal', '<=', $dateTo);
        }

        return $query;
    }

    public function scopeShift(Builder $query, ?string $shift): Builder
    {
        if ($shift && in_array(strtoupper($shift), ['D', 'N'], true)) {
            $query->where('shift', strtoupper($shift));
        }

        return $query;
    }
}
