<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaktaIntegritasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nomorSurat' => $this->nomor_surat,
            'employeeId' => $this->employee_id,
            'nrp' => $this->whenLoaded('employee', fn () => $this->employee?->nrp),
            'nama' => $this->whenLoaded('employee', fn () => $this->employee?->name),
            'jabatan' => $this->whenLoaded('employee', fn () => $this->employee?->jabatan_ss6),
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
            'createdByName' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'createdAt' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
