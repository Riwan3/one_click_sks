<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_type',
        'name',
        'jumlah_unit',
        'mp27',
        'mp29',
        'patterns',
        'index_pengawas',
        'sort_order',
    ];

    protected $casts = [
        'jumlah_unit' => 'integer',
        'mp27' => 'float',
        'mp29' => 'float',
        'index_pengawas' => 'float',
        'sort_order' => 'integer',
    ];
}
