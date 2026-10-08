<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Kontrak data satu Master Unit Fleet ke halaman Master Unit. */
class FleetMasterUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'unit_type' => $this->unit_type,
            'category' => $this->category,
            'is_from_manpower' => $this->source_unit_id !== null,
            'default_group' => $this->default_group,
            'default_area' => $this->default_area,
            'default_front' => $this->default_front,
            'default_refueling_time' => $this->default_refueling_time,
            'default_distance' => $this->default_distance,
            'is_active' => $this->is_active,
        ];
    }
}
