<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviasiRekapPemulanganResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hashid' => $this->getRouteKey(),
            'nrp' => $this->nrp,
            'nama' => $this->nama,
            'jabatan' => $this->jabatan,
            'tanggal' => $this->tanggal instanceof \Carbon\Carbon ? $this->tanggal->format('Y-m-d') : (string) $this->tanggal,
            'shift' => $this->shift,
            'jam' => substr((string) $this->jam, 0, 5),
            'alasan' => $this->alasan,
            'keterangan' => $this->keterangan ?: $this->alasan,
            'konfirmasi' => $this->konfirmasi,
            'createdBy' => $this->created_by,
            'updatedBy' => $this->updated_by,
            'createdAt' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
