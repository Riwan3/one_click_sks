<?php

namespace App\Models;

use App\Enums\ProcedureFollowupType;
use Illuminate\Database\Eloquent\Model;

class ProcedureFollowup extends Model
{
    protected $fillable = [
        'assessment_id',
        'nrp',
        'evaluation_id',
        'type',
        'mentor_id',
        'weak_material_notes',
        'coaching_target_date',
        'remedial_granted_at',
        'remedial_granted_by',
        'created_by',
    ];

    protected $casts = [
        'type' => ProcedureFollowupType::class,
        'coaching_target_date' => 'date',
        'remedial_granted_at' => 'datetime',
    ];

    public function mentor()
    {
        return $this->belongsTo(Employee::class, 'mentor_id');
    }
}
