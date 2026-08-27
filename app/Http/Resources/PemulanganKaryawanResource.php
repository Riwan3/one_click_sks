<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PemulanganKaryawanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'noSurat' => $this->no_surat,
            'employeeId' => $this->employee_id,
            'nrp' => $this->nrp,
            'nama' => $this->nama,
            'jabatan' => $this->jabatan,
            'departemen' => $this->departemen,
            'lokasi' => $this->lokasi,
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'indikasiFatigue' => $this->indikasi_fatigue,
            'jamTidurKurang4jam' => (bool) $this->jam_tidur_kurang_4jam,
            'fatigueBerat' => (bool) $this->fatigue_berat,
            'deskripsiPenyebab' => $this->deskripsi_penyebab,
            'alamatPengantaran' => $this->alamat_pengantaran,
            'penanggungjawabPengantaran' => $this->penanggungjawab_pengantaran,
            'ttdKaryawan' => $this->ttd_karyawan,
            'ttdPengawas' => $this->ttd_pengawas,
            'createdByName' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'createdAt' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
