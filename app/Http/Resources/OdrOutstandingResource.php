<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Baris "Outstanding Activity" di Dashboard Compliance One-Reporting. */
class OdrOutstandingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shift' => $this->shift ?: 'Shift 1',
            'area' => $this->area,
            'unit' => $this->unit,
            'pengawas' => $this->pengawas,
            'aktivitas' => $this->aktivitas,
        ];
    }
}
