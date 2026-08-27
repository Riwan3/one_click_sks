<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoachingClinic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'coach_id',
        'tanggal',
        'lokasi',
        'waktu_pertemuan',
        'materi_pribadi',
        'materi_keluarga',
        'materi_lingkungan',
        'materi_pekerjaan',
        'fatigue_level',
        'deskripsi',
        'rekomendasi',
        'file_bukti',
        'ttd_anak_asuh',
        'ttd_bapak_asuh',
        'created_by',
        'status',
        'coach_email',
        'topik',
        'reminders_sent',
        'last_reminder_sent',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_pertemuan' => 'datetime:H:i',
        'fatigue_level' => 'integer',
        'last_reminder_sent' => 'datetime',
        'reminders_sent' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->whereHas('employee', function (Builder $q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
                ->orWhere('nrp', 'like', "%{$search}%");
        });
    }

    public function scopeDateBetween(Builder $query, ?string $startDate, ?string $endDate): Builder
    {
        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        return $query;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(CoachingClinicProgress::class, 'coaching_clinic_id');
    }
}
