<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachingMateriRevisiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'materi_id' => $this->materi_id,
            'versi' => $this->versi,
            'judul' => $this->judul,
            'ringkasan' => $this->ringkasan,
            'deskripsi' => $this->deskripsi,
            'file_pdf' => $this->file_pdf ? asset('storage/' . $this->file_pdf) : null,
            'video' => $this->video ? asset('storage/' . $this->video) : null,
            'link_sop' => $this->link_sop,
            'thumbnail' => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null,
            'revisi_note' => $this->revisi_note,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', new UserResource($this->creator)),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
