<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachingTahapan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'urutan',
        'durasi_target',
        'wajib',
        'aktif',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'durasi_target' => 'integer',
        'wajib' => 'boolean',
        'aktif' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('urutan');
    }
}
