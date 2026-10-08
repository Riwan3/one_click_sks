<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Kontrak data Setting Fleet harian (hasil FleetSettingService::view) untuk halaman Setting Fleet. */
class FleetDayViewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'groups' => $this['groups'],
            'available_loaders' => FleetMasterUnitResource::collection($this['available_loaders'])->resolve(),
            'available_haulers' => FleetMasterUnitResource::collection($this['available_haulers'])->resolve(),
            'summary' => $this['summary'],
            'warnings' => $this['warnings'],
            'breakdown' => $this['breakdown'],
            'cluster_options' => $this['cluster_options'],
            'area_options' => $this['area_options'],
        ];
    }
}
