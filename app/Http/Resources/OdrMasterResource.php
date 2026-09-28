<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Baris master compliance (area / unit / aktivitas); aktivitas menyertakan dokumen prosedur terlampir. */
class OdrMasterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $base = [
            'id' => $this->id,
            'nama' => $this->nama,
            'aktif' => (bool) $this->aktif,
        ];

        return match ($this->getTable()) {
            'odr_master_unit' => [...$base, 'kode' => $this->kode, 'area' => $this->area, 'keterangan' => $this->keterangan],
            'odr_master_aktivitas' => [...$base, 'kategori' => $this->kategori, 'dokumen' => $this->dokumen ?? [], 'dokumen_urls' => $this->dokumen_urls ?? []],
            default => [...$base, 'keterangan' => $this->keterangan],
        };
    }
}
