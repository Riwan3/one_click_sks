<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoachingMateri extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'kategori',
        'ringkasan',
        'deskripsi',
        'file_pdf',
        'video',
        'link_sop',
        'thumbnail',
        'versi',
    ];

    protected $casts = [
        'versi' => 'integer',
    ];

    public function revisis(): HasMany
    {
        return $this->hasMany(CoachingMateriRevisi::class, 'materi_id');
    }
}
