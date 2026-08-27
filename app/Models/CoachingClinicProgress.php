<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachingClinicProgress extends Model
{
    use HasFactory;

    protected $table = 'coaching_clinic_progress';

    protected $fillable = [
        'coaching_clinic_id',
        'coaching_tahapan_id',
        'progress_pct',
        'notes',
    ];

    protected $casts = [
        'progress_pct' => 'integer',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(CoachingClinic::class, 'coaching_clinic_id');
    }

    public function tahapan(): BelongsTo
    {
        return $this->belongsTo(CoachingTahapan::class, 'coaching_tahapan_id');
    }
}
