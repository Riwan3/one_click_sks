<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QorPerformaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ranking' => $this->ranking,
            'dariOperator' => $this->dari_operator,
            'employeeId' => $this->employee_id,
            'nrp' => $this->whenLoaded('employee', fn () => $this->employee?->nrp),
            'nama' => $this->whenLoaded('employee', fn () => $this->employee?->name),
            'unit' => $this->whenLoaded('employee', fn () => $this->employee?->unit),
            'loader' => $this->loader,
            'kodeUnit' => $this->kode_unit,
            'jarak' => $this->jarak !== null ? (float) $this->jarak : null,
            'ritTotal' => (float) $this->rit_total,
            'bcmTotal' => (float) $this->bcm_total,
        ];
    }
}
