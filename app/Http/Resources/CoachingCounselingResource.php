<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachingCounselingResource extends JsonResource
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
            'coachEmployeeId' => $this->coach_employee_id,
            'coachNama' => $this->whenLoaded('coach', fn () => $this->coach?->name),
            'spId' => $this->sp_id,
            'skId' => $this->sk_id,
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'lokasi' => $this->lokasi,
            'waktuPertemuan' => $this->waktu_pertemuan,
            'materiPribadi' => $this->materi_pribadi,
            'materiKeluarga' => $this->materi_keluarga,
            'materiLingkungan' => $this->materi_lingkungan,
            'materiPekerjaan' => $this->materi_pekerjaan,
            'deskripsi' => $this->deskripsi,
            'rekomendasi' => $this->rekomendasi,
            'fatigueLevel' => (int) $this->fatigue_level,
            'ttdAnakAsuh' => $this->ttd_anak_asuh,
            'ttdBapaAsuh' => $this->ttd_bapa_asuh,
            'fileBukti' => $this->file_bukti ? asset('storage/'.$this->file_bukti) : null,
            'fileSlip' => $this->file_slip ? asset('storage/'.$this->file_slip) : null,
            'createdByName' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'createdAt' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
