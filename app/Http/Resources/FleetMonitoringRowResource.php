<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Satu baris Monitoring Fleet (tanggal + shift) hasil FleetBoardService::monitoring. */
class FleetMonitoringRowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => $this['date'],
            'shift' => $this['shift'],
            'status' => $this['status'],
            'has_setting' => $this['status'] !== 'belum',
            'loaders' => (int) $this['loaders'],
            'haulers' => (int) $this['haulers'],
            'loaders_empty' => (int) $this['loaders_empty'],
            'doubles' => (int) $this['doubles'],
            'updated_at' => $this['updated_at'],
        ];
    }
}
