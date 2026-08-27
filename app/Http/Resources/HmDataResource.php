<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HmDataResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'shift' => $this->shift,
            'employeeId' => $this->employee_id,
            'nrp' => $this->whenLoaded('employee', fn () => $this->employee?->nrp),
            'nama' => $this->whenLoaded('employee', fn () => $this->employee?->name),
            'unit' => $this->unit,
            'dept' => $this->dept,
            'hmStart' => (float) $this->hm_start,
            'hmStop' => (float) $this->hm_stop,
            'hmDiff' => (float) $this->hm_diff,
            'activity' => $this->activity,
            'parking' => $this->parking,
        ];
    }
}
