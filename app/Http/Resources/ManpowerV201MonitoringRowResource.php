<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Satu baris Monitoring Settingan (tanggal + shift) hasil ManpowerV201SettingService::monitoring. */
class ManpowerV201MonitoringRowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => $this['date'],
            'shift' => $this['shift'],
            'hadir' => (int) $this['hadir'],
            'status' => $this['status'],
            'has_setting' => $this['setting'] !== null,
            'summary' => $this['summary'],
            'warnings' => (int) $this['warnings'],
            'updated_by_name' => $this['updated_by_name'],
            'updated_at' => $this['updated_at'],
        ];
    }
}
