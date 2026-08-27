<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkPembinaanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nomorSurat' => $this->nomor_surat,
            'perusahaan' => $this->perusahaan,
            'employeeId' => $this->employee_id,
            'nrp' => $this->whenLoaded('employee', fn () => $this->employee?->nrp),
            'nama' => $this->whenLoaded('employee', fn () => $this->employee?->name),
            'jabatan' => $this->whenLoaded('employee', fn () => $this->employee?->jabatan_ss6),
            'unit' => $this->whenLoaded('employee', fn () => $this->employee?->unit),
            'tanggalPelanggaran' => $this->tanggal_pelanggaran?->format('Y-m-d'),
            'jamPelanggaran' => $this->jam_pelanggaran,
            'lokasi' => $this->lokasi,
            'berlakuSampai' => $this->berlaku_sampai?->format('Y-m-d'),
            'spdkRef' => $this->spdk_ref,
            'dikeluarkanDi' => $this->dikeluarkan_di,
            'dikeluarkanTanggal' => $this->dikeluarkan_tanggal?->format('Y-m-d'),
            'ttdKaryawan' => $this->ttd_karyawan,
            'ttdAtasan' => $this->ttd_atasan,
            'ttdPja' => $this->ttd_pja,
            'ttdHse' => $this->ttd_hse,
            'pengawasId' => $this->pengawas_id,
            'pengawasName' => $this->whenLoaded('pengawas', fn () => $this->pengawas?->name),
            'jenisPelanggaran' => $this->jenis_pelanggaran,
            'lubang' => $this->lubang,
            'keterangan' => $this->keterangan,
            'statusPembinaan' => $this->statusPembinaan(),
            'createdAt' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
