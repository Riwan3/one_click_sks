<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** File evidence (foto/dokumen) sebuah tindakan perbaikan pasca-insiden. */
class OdrIncidentEvidence extends Model
{
    protected $table = 'odr_perbaikan_evidence';

    protected $fillable = [
        'tindakan_id',
        'file_path',
        'original_name',
        'uploaded_by',
    ];

    public function action()
    {
        return $this->belongsTo(OdrIncidentAction::class, 'tindakan_id');
    }
}
