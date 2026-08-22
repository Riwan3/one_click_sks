<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nrp' => $this->nrp,
            'name' => $this->name,
            'jabatanSs6' => $this->jabatan_ss6,
            'jabatanRoster' => $this->jabatan_roster,
            'statusKaryawan' => $this->status_karyawan->value,
            'lokasiKerja' => $this->lokasi_kerja,
            'unit' => $this->unit,
            'noTelepon' => $this->no_telepon,
            'email' => $this->email,
            'efektifBekerja' => $this->efektif_bekerja?->format('Y-m-d'),
            'inputHere' => $this->input_here,
            'tenure' => $this->tenureLabel(),
        ];
    }
}
