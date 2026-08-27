<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuratPeringatanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nomorSp' => $this->nomor_sp,
            'employeeId' => $this->employee_id,
            'nrp' => $this->whenLoaded('employee', fn () => $this->employee?->nrp),
            'nama' => $this->whenLoaded('employee', fn () => $this->employee?->name),
            'jabatan' => $this->whenLoaded('employee', fn () => $this->employee?->jabatan_ss6),
            'unit' => $this->whenLoaded('employee', fn () => $this->employee?->unit),
            'noTelepon' => $this->whenLoaded('employee', fn () => $this->employee?->no_telepon),
            'kodePelanggaran' => $this->kode_pelanggaran,
            'jenisPembinaan' => $this->jenis_pembinaan->value,
            'detailPelanggaran' => $this->detail_pelanggaran,
            'tanggalKejadian' => $this->tanggal_kejadian?->format('Y-m-d'),
            'jamKejadian' => $this->jam_kejadian,
            'tempatKejadian' => $this->tempat_kejadian,
            'pelanggaranAbsenTerlambat' => (bool) $this->pelanggaran_absen_terlambat,
            'pelanggaranPerilaku' => (bool) $this->pelanggaran_perilaku,
            'pelanggaranCerobohLalai' => (bool) $this->pelanggaran_ceroboh_lalai,
            'pelanggaranHasilKerja' => (bool) $this->pelanggaran_hasil_kerja,
            'pelanggaranMenolakPerintah' => (bool) $this->pelanggaran_menolak_perintah,
            'pelanggaranPeraturan' => (bool) $this->pelanggaran_peraturan,
            'peringatanSebelumnyaStatus' => $this->peringatan_sebelumnya_status,
            'peringatanSebelumnyaJenis' => $this->peringatan_sebelumnya_jenis,
            'peringatanSebelumnyaMengenai' => $this->peringatan_sebelumnya_mengenai,
            'sanksiMasaBerlaku' => $this->sanksi_masa_berlaku,
            'dikeluarkanDi' => $this->dikeluarkan_di,
            'dikeluarkanTanggal' => $this->dikeluarkan_tanggal?->format('Y-m-d'),
            'catatanKaryawan' => $this->catatan_karyawan,
            'ttdKaryawan' => $this->ttd_karyawan,
            'ttdAtasan' => $this->ttd_atasan,
            'pengawasId' => $this->pengawas_id,
            'pengawasName' => $this->whenLoaded('pengawas', fn () => $this->pengawas?->name),
            'koreksiAlpa' => $this->koreksi_alpa,
            'adminName' => $this->whenLoaded('admin', fn () => $this->admin?->name),
            'createdAt' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
