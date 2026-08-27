<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachingClinicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', new EmployeeResource($this->employee)),
            'coach_id' => $this->coach_id,
            'coach' => $this->whenLoaded('coach', new UserResource($this->coach)),
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'lokasi' => $this->lokasi,
            'waktu_pertemuan' => $this->waktu_pertemuan,
            'materi_pribadi' => $this->materi_pribadi,
            'materi_keluarga' => $this->materi_keluarga,
            'materi_lingkungan' => $this->materi_lingkungan,
            'materi_pekerjaan' => $this->materi_pekerjaan,
            'fatigue_level' => $this->fatigue_level,
            'deskripsi' => $this->deskripsi,
            'rekomendasi' => $this->rekomendasi,
            'file_bukti' => $this->file_bukti ? asset('storage/' . $this->file_bukti) : null,
            'ttd_anak_asuh' => $this->ttd_anak_asuh,
            'ttd_bapak_asuh' => $this->ttd_bapak_asuh,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', new UserResource($this->creator)),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
