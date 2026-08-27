<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OperatorUnfitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nrp' => $this->nrp,
            'nama' => $this->nama,
            'perusahaan' => $this->perusahaan,
            'departemen' => $this->departemen,
            'contact' => $this->contact,
            'diagnosa' => $this->diagnosa,
            'dokterRujukan' => $this->dokter_rujukan,
            'tglMulaiUnfit' => $this->tgl_mulai_unfit?->format('Y-m-d'),
            'tglAkhirUnfit' => $this->tgl_akhir_unfit?->format('Y-m-d'),
            'laporan' => $this->laporan,
            'buktiUrl' => $this->bukti ? Storage::disk('public')->url($this->bukti) : null,
            'isActive' => $this->isActive(),
        ];
    }
}
