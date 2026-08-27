<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NomorPenting extends Model
{
    use HasFactory;

    protected $table = 'nomor_penting';

    protected $fillable = [
        'nama',
        'jabatan',
        'no_telepon',
        'kategori',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'aktif' => 'boolean',
    ];
}
