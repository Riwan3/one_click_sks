<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Satu baris tabel MONCU Monitoring (hasil MoncuMonitoringService). */
class MoncuMonitoringRowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'nrp' => (string) $this['nrp'],
            'name' => $this['name'],
            'jabatan' => $this['jabatan'],
            'group' => $this['group'],
            'status' => $this['status'],
            'awal' => $this['awal'],
            'akhir' => $this['akhir'],
            'induksi' => $this['induksi'],
            'plan' => $this['plan'],
            'plan_is_ref' => $this['plan_is_ref'],
            'in_roster' => $this['in_roster'],
        ];
    }
}
