<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuratTeguranResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getRouteKey(),
            'rawId' => $this->id,
            'hashid' => $this->getRouteKey(),
            'noSurat' => $this->no_surat,
            'employeeId' => $this->employee_id,
            'nrp' => $this->nrp,
            'nama' => $this->nama,
            'jabatan' => $this->jabatan,
            'departemen' => $this->departemen,
            'tanggalKejadian' => $this->tanggal_kejadian?->format('Y-m-d'),
            'tempatKejadian' => $this->tempat_kejadian,
            'jenisAbsen' => (bool) $this->jenis_absen,
            'jenisPerilaku' => (bool) $this->jenis_perilaku,
            'jenisCeroboh' => (bool) $this->jenis_ceroboh,
            'jenisHasilKerja' => (bool) $this->jenis_hasil_kerja,
            'jenisMenolakPerintah' => (bool) $this->jenis_menolak_perintah,
            'jenisPelanggaranPeraturan' => (bool) $this->jenis_pelanggaran_peraturan,
            'penjelasanSingkat' => $this->penjelasan_singkat,
            'kejadianTanggal' => $this->kejadian_tanggal?->format('Y-m-d'),
            'kejadianJam' => $this->kejadian_jam,
            'kejadianLokasi' => $this->kejadian_lokasi,
            'dikeluarkanDi' => $this->dikeluarkan_di,
            'dikeluarkanTanggal' => $this->dikeluarkan_tanggal?->format('Y-m-d'),
            'docRank' => $this->doc_rank,
            'ttdKaryawan' => $this->ttd_karyawan,
            'ttdAtasan' => $this->ttd_atasan,
            'ttdAtasanAtasan' => $this->ttd_atasan_atasan,
            'catatan' => $this->catatan,
            'foto' => $this->foto_url,
            'noTelepon' => $this->whenLoaded('employee', fn () => $this->employee?->no_telepon),
            'createdByName' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'updatedByName' => $this->whenLoaded('updater', fn () => $this->updater?->name),
            'createdAt' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
