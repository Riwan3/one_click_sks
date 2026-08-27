<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class IzinKaryawanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'jenisIzin' => $this->jenis_izin->value,
            'jenisIzinLabel' => $this->jenis_izin->label(),
            'noSurat' => $this->no_surat,
            'nama' => $this->nama,
            'nrp' => $this->nrp,
            'jabatan' => $this->jabatan,
            'departemen' => $this->departemen,
            'alasan' => $this->alasan,
            'tujuan' => $this->tujuan,
            'alamat' => $this->alamat,
            'tglBerangkat' => $this->tgl_berangkat?->format('Y-m-d'),
            'tglKembali' => $this->tgl_kembali?->format('Y-m-d'),
            'noTelepon' => $this->no_telepon,
            'keluhan' => $this->keluhan,
            'hasilTd' => $this->hasil_td,
            'hasilRr' => $this->hasil_rr,
            'hasilT' => $this->hasil_t,
            'hasilP' => $this->hasil_p,
            'hasilSpo' => $this->hasil_spo,
            'diagnosa' => $this->diagnosa,
            'terapi' => $this->terapi,
            'keteranganStatus' => $this->keterangan_status,
            'rujukKe' => $this->rujuk_ke,
            'rumahSakitRujukan' => $this->rumah_sakit_rujukan,
            'dokterRujukan' => $this->dokter_rujukan,
            'dipulangkanTidur' => (bool) $this->dipulangkan_tidur,
            'buktiUrl' => $this->file_bukti ? Storage::disk('public')->url($this->file_bukti) : null,
            'status' => $this->status->value,
            'statusLabel' => $this->status->label(),
            'approverName' => $this->whenLoaded('approver', fn () => $this->approver?->name),
            'approvedAt' => $this->approved_at?->format('Y-m-d H:i'),
            'createdAt' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
