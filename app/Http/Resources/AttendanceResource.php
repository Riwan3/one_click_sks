<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employeeId' => $this->employee_id,
            'nrp' => $this->whenLoaded('employee', fn () => $this->employee?->nrp),
            'nama' => $this->whenLoaded('employee', fn () => $this->employee?->name),
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'status' => $this->status,
            'jamMasuk' => $this->jam_masuk,
            'jamKeluar' => $this->jam_keluar,
            'keterangan' => $this->keterangan,
        ];
    }
}
