<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcedureCertificateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $employee = $this->employee;

        return [
            'id' => $this->id,
            'certificate_code' => $this->certificate_code,
            'nrp' => $this->nrp,
            'name' => $employee?->name ?? 'NRP '.$this->nrp,
            'jabatan' => $employee?->jabatan_ss6 ?: $employee?->jabatan_roster,
            'procedure_title' => $this->assessment?->procedure?->title,
            'assessment_title' => $this->assessment?->title,
            'passing_grade' => (int) ($this->assessment?->passing_grade ?? 0),
            'score' => $this->score,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'valid_until' => $this->isLifetime() ? null : $this->valid_until?->format('Y-m-d'),
            'is_lifetime' => $this->isLifetime(),
            'days_left' => $this->isLifetime() ? null : (int) now()->startOfDay()->diffInDays($this->valid_until, false),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'pdf_url' => route('one-procedure.evaluasi.certificates.pdf', $this->certificate_code),
            'verify_url' => route('procedure-certificates.verify', ['code' => $this->certificate_code]),
        ];
    }
}
