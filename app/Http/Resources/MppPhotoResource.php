<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MppPhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'jenis' => $this->jenis,
            'unit_code' => $this->unit_code,
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'shift' => $this->shift,
            'nrp' => $this->nrp,
            'nama' => $this->nama,
            'url' => $this->url,
            'watermark_data' => $this->watermark_data,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'creator' => $this->whenLoaded('creator', $this->creator?->name),
        ];
    }
}
