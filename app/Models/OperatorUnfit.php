<?php

namespace App\Models;

use App\Models\Concerns\HasHashid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OperatorUnfit extends Model
{
    use HasHashid;
    protected $fillable = [
        'nrp',
        'nama',
        'perusahaan',
        'departemen',
        'contact',
        'diagnosa',
        'dokter_rujukan',
        'tgl_mulai_unfit',
        'tgl_akhir_unfit',
        'laporan',
        'bukti',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tgl_mulai_unfit' => 'date',
            'tgl_akhir_unfit' => 'date',
        ];
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('nrp', 'like', "%{$keyword}%")
                ->orWhere('nama', 'like', "%{$keyword}%")
                ->orWhere('diagnosa', 'like', "%{$keyword}%")
                ->orWhere('dokter_rujukan', 'like', "%{$keyword}%")
        );
    }

    public function scopeActiveOnly(Builder $query): Builder
    {
        return $query->where(
            fn (Builder $q) => $q->whereNull('tgl_akhir_unfit')->orWhereDate('tgl_akhir_unfit', '>', now())
        );
    }

    public function scopeFinishedOnly(Builder $query): Builder
    {
        return $query->whereNotNull('tgl_akhir_unfit')->whereDate('tgl_akhir_unfit', '<=', now());
    }

    public function isActive(): bool
    {
        return $this->tgl_akhir_unfit === null || $this->tgl_akhir_unfit->isFuture();
    }
}
