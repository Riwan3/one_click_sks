<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MorRanking extends Model
{
    protected $table = 'mor_ranking';

    protected $fillable = [
        'nrp',
        'nama',
        'tahun',
        'quartal',
        'jabatan',
        'kategori',
        'grup',
        'atr_persen',
        'atr_point',
        'ts_persen',
        'ts_point',
        'hm_persen',
        'hm_point',
        'pdty_persen',
        'pdty_point',
        'fuel_point',
        'pembinaan_point',
        'total_poin',
        'ranking',
        'fuel_pending',
        'pembinaan_pending',
        'grup_unmapped',
    ];

    protected function casts(): array
    {
        return [
            'atr_persen' => 'decimal:2',
            'atr_point' => 'decimal:2',
            'ts_persen' => 'decimal:2',
            'ts_point' => 'decimal:2',
            'hm_persen' => 'decimal:2',
            'hm_point' => 'decimal:2',
            'pdty_persen' => 'decimal:4',
            'pdty_point' => 'decimal:2',
            'fuel_point' => 'decimal:2',
            'pembinaan_point' => 'decimal:2',
            'total_poin' => 'decimal:2',
            'fuel_pending' => 'boolean',
            'pembinaan_pending' => 'boolean',
            'grup_unmapped' => 'boolean',
        ];
    }

    public function scopePeriod(Builder $query, int $tahun, int $quartal): Builder
    {
        return $query->where('tahun', $tahun)->where('quartal', $quartal);
    }
}
