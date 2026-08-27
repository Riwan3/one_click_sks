<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeritaAcaraResource extends JsonResource
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
            'kronologi' => $this->kronologi,
            'diketahuiOlehId' => $this->diketahui_oleh_id,
            'diketahuiOlehNama' => $this->whenLoaded('diketahuiOleh', fn () => $this->diketahuiOleh?->name),
            'ttdKaryawan' => $this->ttd_karyawan,
            'ttdAtasan' => $this->ttd_atasan,
            'noDokumen' => $this->no_dokumen,
            'revisi' => $this->revisi,
            'tglEfektif' => $this->tgl_efektif?->format('Y-m-d'),
            'createdByName' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'createdAt' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
