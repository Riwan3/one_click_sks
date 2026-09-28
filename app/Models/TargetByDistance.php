<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetByDistance extends Model
{
    protected $table = 'target_by_distance';

    protected $fillable = [
        'kategori',
        'kombinasi',
        'jarak_min',
        'target_pdty',
    ];

    protected function casts(): array
    {
        return [
            'jarak_min' => 'decimal:2',
            'target_pdty' => 'decimal:4',
        ];
    }
}
