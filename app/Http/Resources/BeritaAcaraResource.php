<?php

namespace App\Http\Resources;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeritaAcaraResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $siteCode = Setting::getValue('berita_acara_site_code');
        $nomorSurat = $this->nomor_surat;
        if ($siteCode && $nomorSurat) {
            $nomorSurat = preg_replace('#(/BA/PPA/)[^/]+(/PRO/)#i', '$1'.$siteCode.'$2', $nomorSurat);
        }

        $globalNoDokumen = Setting::getValue('berita_acara_no_dokumen');
        $globalRevisi = Setting::getValue('berita_acara_revisi');
        $globalTglEfektif = Setting::getValue('berita_acara_tgl_efektif');

        return [
            'id' => $this->getRouteKey(),
            'rawId' => $this->id,
            'hashid' => $this->getRouteKey(),
            'nomorSurat' => $nomorSurat,
            'employeeId' => $this->employee_id,
            'nrp' => $this->employee?->nrp ?? '-',
            'nama' => $this->employee?->name ?? '-',
            'jabatan' => $this->employee?->jabatan_ss6 ?? '-',
            'jenisPembinaan' => 'Berita Acara',
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'kronologi' => $this->kronologi,
            'detail' => $this->kronologi,
            'diketahuiOlehId' => $this->diketahui_oleh_id,
            'diketahuiOlehNama' => $this->diketahuiOleh?->name ?? '-',
            'pengawas' => $this->diketahuiOleh?->name ?? '-',
            'ttdKaryawan' => $this->ttd_karyawan,
            'ttdAtasan' => $this->ttd_atasan,
            'noDokumen' => $globalNoDokumen ?: $this->no_dokumen,
            'revisi' => $globalRevisi ?: $this->revisi,
            'tglEfektif' => ($globalTglEfektif ? date('Y-m-d', strtotime($globalTglEfektif)) : null) ?: $this->tgl_efektif?->format('Y-m-d'),
            'createdByName' => $this->creator?->name ?? $this->creator?->username ?? 'Admin',
            'createdAt' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
