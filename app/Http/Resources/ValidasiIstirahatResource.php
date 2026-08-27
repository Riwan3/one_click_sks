<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ValidasiIstirahatResource extends JsonResource
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
            'alasanKurangTidur' => $this->alasan_kurang_tidur,
            'alasanKurangTidurBerulang' => $this->alasan_kurang_tidur_berulang,
            'kendalaTidur' => $this->kendala_tidur,
            'tinggalDi' => $this->tinggal_di,
            'namaMess' => $this->nama_mess,
            'alamatNonMess' => $this->alamat_non_mess,
            'statusPerkawinan' => $this->status_perkawinan,
            'jumlahAnakSerumah' => $this->jumlah_anak_serumah,
            'anakUsiaKurang2th' => $this->anak_usia_kurang_2th,
            'pekerjaanSampingan' => $this->pekerjaan_sampingan,
            'keterangan' => $this->keterangan,
            'tanggalValidasi' => $this->tanggal_validasi?->format('Y-m-d'),
            'ttdKaryawan' => $this->ttd_karyawan,
            'ttdPengawas' => $this->ttd_pengawas,
            'namaPengawas' => $this->nama_pengawas,
            'createdByName' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'createdAt' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
