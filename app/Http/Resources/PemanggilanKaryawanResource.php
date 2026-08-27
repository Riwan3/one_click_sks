<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PemanggilanKaryawanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employeeId' => $this->employee_id,
            'nrp' => $this->nrp,
            'nama' => $this->nama,
            'jabatan' => $this->jabatan,
            'alasanPemanggilan' => $this->alasan_pemanggilan,
            'tempat' => $this->tempat,
            'jamPemanggilan' => $this->jam_pemanggilan,
            'tanggalPemanggilan' => $this->tanggal_pemanggilan?->format('Y-m-d'),
            'namaManpower' => $this->nama_manpower,
            'fatiguePeriode' => $this->fatigue_periode,
            'ttdPemanggil' => $this->ttd_pemanggil,
            'ttdKaryawan' => $this->ttd_karyawan,
            'createdByName' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'createdAt' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
