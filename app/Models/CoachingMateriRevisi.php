<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachingMateriRevisi extends Model
{
    use HasFactory;

    protected $fillable = [
        'materi_id',
        'versi',
        'judul',
        'ringkasan',
        'deskripsi',
        'file_pdf',
        'video',
        'link_sop',
        'thumbnail',
        'revisi_note',
        'created_by',
    ];

    protected $casts = [
        'versi' => 'integer',
    ];

    public function materi(): BelongsTo
    {
        return $this->belongsTo(CoachingMateri::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
