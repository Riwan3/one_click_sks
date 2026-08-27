<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    use HasFactory;

    protected $table = 'procedures';

    protected $fillable = [
        'title',
        'category',
        'revision',
        'description',
        'risk_level',
        'status',
        'file_path',
        'file_type',
        'effective_date',
        'expired_date',
        'created_by',
    ];

    public function assessments()
    {
        return $this->hasMany(ProcedureAssessment::class, 'procedure_id');
    }
}
