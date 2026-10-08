<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Lembar Papan Fleet hasil FleetBoardService::sheet (kontrak props `fleetPreview.sheet`). */
class FleetBoardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => $this['date'],
            'shift' => $this['shift'],
            'areas' => $this['areas'],
            'breakdown' => $this['breakdown'],
            'ready' => $this['ready'],
            'summary' => $this['summary'],
            'total_hauler' => (int) $this['total_hauler'],
        ];
    }
}
