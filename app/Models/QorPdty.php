<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QorPdty extends Model
{
    protected $table = 'qor_pdty';

    protected $fillable = [
        'nrp',
        'nama',
        'tahun',
        'quartal',
        'kategori',
        'hm_hd785_395',
        'hm_c777_395',
        'hm_hd785_1250',
        'hm_hd785_jumbo',
        'hm_c777_1250',
        'hm_hd785_2000',
        'hm_c777_2000',
        'hm_e1250',
        'hm_e6108',
        'hm_e395',
        'hm_e2000',
        'act_hd785_395',
        'act_c777_395',
        'act_hd785_1250',
        'act_hd785_jumbo',
        'act_c777_1250',
        'act_hd785_2000',
        'act_c777_2000',
        'act_e1250',
        'act_e6108',
        'act_e395',
        'act_e2000',
        'pdty_hd785_395',
        'pdty_c777_395',
        'pdty_hd785_1250',
        'pdty_hd785_jumbo',
        'pdty_c777_1250',
        'pdty_hd785_2000',
        'pdty_c777_2000',
        'pdty_e1250',
        'pdty_e6108',
        'pdty_e395',
        'pdty_e2000',
        'target_hd785_395',
        'target_c777_395',
        'target_hd785_1250',
        'target_hd785_jumbo',
        'target_c777_1250',
        'target_hd785_2000',
        'target_c777_2000',
        'target_e1250',
        'target_e6108',
        'target_e395',
        'target_e2000',
        'dist_bucket_395',
        'dist_bucket_1250',
        'dist_bucket_2000',
        'hm_total',
        'sumproduct_target',
        'sumproduct_act',
        'achievement_pct',
        'achievement_combined',
        'target_unresolved',
    ];

    protected function casts(): array
    {
        return [
            'achievement_pct' => 'decimal:4',
            'achievement_combined' => 'decimal:4',
            'hm_total' => 'decimal:2',
            'sumproduct_target' => 'decimal:4',
            'sumproduct_act' => 'decimal:4',
        ];
    }

    public function scopePeriod(Builder $query, int $tahun, int $quartal): Builder
    {
        return $query->where('tahun', $tahun)->where('quartal', $quartal);
    }
}
