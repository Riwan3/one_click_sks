<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MasterSpdk extends Model
{
    protected $fillable = [
        'no_pelanggaran',
        'kategori',
        'jenis_pelanggaran',
        'sanksi',
        'lubang',
        'keterangan_sanksi',
    ];

    protected function casts(): array
    {
        return [
            'lubang' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('no_pelanggaran', 'like', "%{$keyword}%")
                ->orWhere('kategori', 'like', "%{$keyword}%")
                ->orWhere('jenis_pelanggaran', 'like', "%{$keyword}%")
                ->orWhere('sanksi', 'like', "%{$keyword}%")
                ->orWhere('keterangan_sanksi', 'like', "%{$keyword}%")
        );
    }
}
