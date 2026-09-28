<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PemanggilanKaryawanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $eviden1 = $this->eviden_1;
        if ($eviden1 && ! str_starts_with($eviden1, 'http')) {
            $eviden1 = '/storage/' . ltrim($eviden1, '/');
        }

        $eviden2 = $this->eviden_2;
        if ($eviden2 && ! str_starts_with($eviden2, 'http')) {
            $eviden2 = '/storage/' . ltrim($eviden2, '/');
        }

        return [
            'id' => $this->getRouteKey(),
            'rawId' => $this->id,
            'hashid' => $this->getRouteKey(),
            'noSurat' => $this->no_surat,
            'employeeId' => $this->employee_id,
            'nrp' => $this->nrp,
            'nama' => $this->nama,
            'jabatan' => $this->jabatan,
            'alasanPemanggilan' => $this->alasan_pemanggilan,
            'tempat' => $this->tempat,
            'jamPemanggilan' => $this->jam_pemanggilan,
            'tanggalPemanggilan' => $this->tanggal_pemanggilan?->format('Y-m-d'),
            'ttdPemanggilan' => $this->ttd_pemanggilan,
            'namaManpower' => $this->nama_manpower,
            'fatiguePeriode' => $this->fatigue_periode,
            'ttdPemanggil' => $this->ttd_pemanggil,
            'ttdKaryawan' => $this->ttd_karyawan,
            'eviden1' => $eviden1,
            'eviden2' => $eviden2,
            'foto' => $this->foto_url,
            'noTelepon' => $this->whenLoaded('employee', fn () => $this->employee?->no_telepon),
            'createdByName' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'updatedByName' => $this->whenLoaded('updater', fn () => $this->updater?->name),
            'createdAt' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
