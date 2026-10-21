<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpdMinimalGradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit' => $this->unit,
            'grade_min_max' => $this->grade_min_max,
            'grade_min' => $this->grade_min,
            'cluster_unit' => $this->cluster_unit,
        ];
    }
}
