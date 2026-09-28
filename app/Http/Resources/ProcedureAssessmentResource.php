<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcedureAssessmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $targets = $this->target_roles ?: [];

        return [
            'id' => $this->id,
            'procedure_id' => $this->procedure_id,
            'procedure_title' => $this->procedure?->title,
            'title' => $this->title,
            'passing_grade' => $this->passing_grade,
            'timer_minutes' => $this->timer_minutes,
            'retry_limit' => $this->retry_limit,
            'validity_months' => $this->validity_months,
            'randomize_questions' => $this->randomize_questions,
            'mandatory_read' => $this->mandatory_read,
            'status' => $this->status,
            'target_roles' => $targets,
            'total_questions' => (int) $this->questions_count,
            'total_targets' => count($targets),
            'signed_by' => $this->signed_by,
            'is_signed' => $this->signer_signed_at !== null,
            'created_at' => $this->created_at?->format('Y-m-d'),
        ];
    }
}
