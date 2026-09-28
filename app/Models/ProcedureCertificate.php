<?php

namespace App\Models;

use App\Enums\ProcedureCertificateStatus;
use Illuminate\Database\Eloquent\Model;

class ProcedureCertificate extends Model
{
    /** valid_until untuk sertifikat tanpa masa berlaku (Selamanya), sama seperti v1. */
    public const LIFETIME_DATE = '9999-12-31';

    protected $fillable = [
        'assessment_id',
        'evaluation_id',
        'nrp',
        'certificate_code',
        'score',
        'issue_date',
        'valid_until',
        'status',
        'issued_by',
    ];

    protected $casts = [
        'score' => 'float',
        'issue_date' => 'date',
        'valid_until' => 'date',
        'status' => ProcedureCertificateStatus::class,
    ];

    public function assessment()
    {
        return $this->belongsTo(ProcedureAssessment::class, 'assessment_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'nrp', 'nrp');
    }

    public function isLifetime(): bool
    {
        return $this->valid_until?->toDateString() === self::LIFETIME_DATE;
    }
}
