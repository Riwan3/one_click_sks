<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Dokumen prosedur (prosedur_kerja) untuk tabel ringkas di Dashboard One-Procedure. */
class ProsedurDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_dokumen' => $this->kode_dokumen,
            'judul' => $this->judul,
            'jenis_label' => $this->jenis_label,
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'status' => $this->status ?? 'aktif',
            'has_file' => (bool) $this->file_dokumen,
        ];
    }
}
