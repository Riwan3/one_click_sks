<?php

namespace App\Models;

use App\Enums\OdrIncidentActionType;
use Illuminate\Database\Eloquent\Model;

/** Tindakan pencegahan / mitigasi sebuah insiden. */
class OdrIncidentAction extends Model
{
    protected $table = 'odr_perbaikan_tindakan';

    protected $fillable = [
        'insiden_id',
        'jenis',
        'uraian_tindakan',
        'pic',
    ];

    protected $casts = [
        'jenis' => OdrIncidentActionType::class,
    ];

    public function incident()
    {
        return $this->belongsTo(OdrIncident::class, 'insiden_id');
    }

    public function evidences()
    {
        return $this->hasMany(OdrIncidentEvidence::class, 'tindakan_id')->orderBy('id');
    }
}
