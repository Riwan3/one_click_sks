<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MasterSpdk extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'no_pelanggaran',
        'kategori',
        'jenis_pelanggaran',
        'sanksi',
        'keterangan_sanksi',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('jenis_pelanggaran', 'like', "%{$keyword}%")
                ->orWhere('sanksi', 'like', "%{$keyword}%")
        );
    }
}
