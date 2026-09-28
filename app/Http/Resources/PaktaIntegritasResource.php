<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaktaIntegritasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getRouteKey(),
            'rawId' => $this->id,
            'hashid' => $this->getRouteKey(),
            'nomorSurat' => $this->nomor_surat,
            'employeeId' => $this->employee_id,
            'nrp' => $this->whenLoaded('employee', fn () => $this->employee?->nrp),
            'nama' => $this->whenLoaded('employee', fn () => $this->employee?->name),
            'jabatan' => $this->whenLoaded('employee', fn () => $this->employee?->jabatan_ss6),
            'unit' => $this->whenLoaded('employee', fn () => $this->employee?->unit),
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'tempat' => $this->tempat,
            'pernyataan' => $this->pernyataan,
            'atasanNama' => $this->atasan_nama,
            'mengetahuiNama' => $this->mengetahui_nama,
            'cc' => $this->cc,
            'arsip' => $this->arsip,
            'ttdKaryawan' => $this->ttd_karyawan,
            'ttdAtasan' => $this->ttd_atasan,
            'ttdMengetahui' => $this->ttd_mengetahui,
            'foto' => $this->foto_url,
            'createdByName' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'createdAt' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
