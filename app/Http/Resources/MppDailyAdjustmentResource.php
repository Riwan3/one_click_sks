<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MppDailyAdjustmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'shift' => $this->shift,
            'kategori' => $this->kategori,
            'jumlah' => $this->jumlah,
            'sakit' => $this->sakit,
            'izin' => $this->izin,
            'alfa' => $this->alfa,
            'training' => $this->training,
            'induksi' => $this->induksi,
            'tidak_checkin' => $this->tidak_checkin,
            'total_deductions' => $this->total_deductions,
            'actual_mp' => $this->actual_mp,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
