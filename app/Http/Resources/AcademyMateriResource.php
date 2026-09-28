<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademyMateriResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category,
            'revision' => $this->revision,
            'description' => $this->description,
            'risk_level' => $this->risk_level,
            'status' => $this->status,
            'file_path' => $this->file_path,
            'file_type' => $this->file_path ? $this->file_type : null,
            'effective_date' => $this->effective_date?->format('Y-m-d'),
            'expired_date' => $this->expired_date?->format('Y-m-d'),
            'creator_name' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d'),
            'total_questions' => (int) $this->total_questions,
            'total_assigned' => (int) $this->total_assigned,
            'total_completed' => (int) $this->total_completed,
        ];
    }
}
