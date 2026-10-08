<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Satu baris karyawan pada halaman MONCU (hasil MoncuService::getOverview). */
class MoncuRowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'nrp' => (string) $this['nrp'],
            'name' => $this['name'],
            'jabatan' => $this['jabatan'],
            'group_name' => $this['group_name'],
            'hiring_date' => $this['hiring_date'],
            'interval_override' => $this['interval_override'],
            'notes' => $this['notes'],
            'entitlement' => $this['entitlement'],
            'used' => $this['used'],
            'balance' => $this['balance'],
            'rosters' => $this['rosters'],
            'periods' => collect($this['periods'])->map(fn ($p, $no) => ['period_no' => $no] + $p)->values(),
        ];
    }
}
