<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachingMateriResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'kategori' => $this->kategori,
            'ringkasan' => $this->ringkasan,
            'deskripsi' => $this->deskripsi,
            'file_pdf' => $this->file_pdf ? asset('storage/' . $this->file_pdf) : null,
            'video' => $this->video ? asset('storage/' . $this->video) : null,
            'link_sop' => $this->link_sop,
            'thumbnail' => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null,
            'versi' => $this->versi,
            'revisis' => CoachingMateriRevisiResource::collection($this->whenLoaded('revisis')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
