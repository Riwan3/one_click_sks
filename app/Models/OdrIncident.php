<?php

namespace App\Models;

use App\Enums\OdrIncidentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Data insiden pada menu Perbaikan Pasca-Insiden One-Reporting. */
class OdrIncident extends Model
{
    protected $table = 'odr_perbaikan_pasca_insiden';

    protected $fillable = [
        'tanggal_insiden',
        'judul_insiden',
        'area',
        'deskripsi_insiden',
        'target_selesai',
        'status',
    ];

    protected $casts = [
        'tanggal_insiden' => 'date',
        'target_selesai' => 'date',
        'status' => OdrIncidentStatus::class,
    ];

    public function actions()
    {
        return $this->hasMany(OdrIncidentAction::class, 'insiden_id')->orderBy('id');
    }

    /** Belum selesai dan target selesai sudah lewat. */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', '!=', OdrIncidentStatus::Selesai)->whereNotNull('target_selesai')->whereDate('target_selesai', '<', now()->toDateString());
    }

    public function isOverdue(): bool
    {
        return $this->status !== OdrIncidentStatus::Selesai && $this->target_selesai?->lt(now()->startOfDay());
    }
}
