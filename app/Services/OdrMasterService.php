<?php

namespace App\Services;

use App\Models\OdrMasterAktivitas;
use App\Models\OdrMasterArea;
use App\Models\OdrMasterUnit;
use App\Models\ProsedurKerja;
use Illuminate\Support\Collection;

/** Master Data Compliance One-Reporting: area, unit/lokasi & aktivitas (padanan master_compliance.php v1). */
class OdrMasterService
{
    public const TABS = ['area', 'unit', 'aktivitas'];

    public function areas(): Collection
    {
        return OdrMasterArea::orderBy('nama')->get();
    }

    public function units(): Collection
    {
        return OdrMasterUnit::orderBy('area')->orderBy('nama')->get();
    }

    /** Aktivitas beserta path & URL dokumen terlampir (di-resolve sekali untuk semua baris). */
    public function aktivitas(): Collection
    {
        $rows = OdrMasterAktivitas::orderBy('kategori')->orderBy('nama')->get();
        $paths = $rows->flatMap(fn ($row) => $this->documentPaths($row->keterangan))->unique()->values()->all();
        $urls = array_combine($paths, $this->documentUrls($paths)) ?: [];

        return $rows->each(function (OdrMasterAktivitas $row) use ($urls) {
            $rowPaths = $this->documentPaths($row->keterangan);
            $row->setAttribute('dokumen', $rowPaths);
            $row->setAttribute('dokumen_urls', array_map(fn ($p) => $urls[$p], $rowPaths));
        });
    }

    public function activeAreaNames(): Collection
    {
        return OdrMasterArea::where('aktif', true)->orderBy('nama')->pluck('nama');
    }

    public function kategoriOptions(): Collection
    {
        return OdrMasterAktivitas::whereNotNull('kategori')->where('kategori', '!=', '')->distinct()->orderBy('kategori')->pluck('kategori');
    }

    /** Dokumen prosedur yang bisa dilampirkan ke aktivitas (menu One-Procedure > Prosedur). */
    public function documentOptions(): Collection
    {
        return ProsedurKerja::whereNotNull('file_dokumen')->where('file_dokumen', '!=', '')->orderBy('judul')->get(['id', 'judul', 'file_dokumen']);
    }

    public function saveArea(array $data, ?OdrMasterArea $area = null): OdrMasterArea
    {
        return $this->persist($area ?? new OdrMasterArea, [
            'nama' => trim($data['nama']),
            'keterangan' => trim((string) ($data['keterangan'] ?? '')),
        ], $data);
    }

    public function saveUnit(array $data, ?OdrMasterUnit $unit = null): OdrMasterUnit
    {
        return $this->persist($unit ?? new OdrMasterUnit, [
            'kode' => trim((string) ($data['kode'] ?? '')),
            'nama' => trim($data['nama']),
            'area' => trim((string) ($data['area'] ?? '')),
            'keterangan' => trim((string) ($data['keterangan'] ?? '')),
        ], $data);
    }

    /** Dokumen terpilih disimpan di kolom keterangan sebagai JSON array path file, sama seperti v1. */
    public function saveAktivitas(array $data, ?OdrMasterAktivitas $aktivitas = null): OdrMasterAktivitas
    {
        $documents = array_values(array_filter($data['dokumen'] ?? []));

        return $this->persist($aktivitas ?? new OdrMasterAktivitas, [
            'nama' => trim($data['nama']),
            'kategori' => trim((string) ($data['kategori'] ?? '')),
            'keterangan' => $documents ? json_encode($documents, JSON_UNESCAPED_SLASHES) : null,
        ], $data);
    }

    /** Path dokumen dari keterangan aktivitas (JSON array; data lama berupa teks tunggal). */
    public function documentPaths(?string $keterangan): array
    {
        if (! $keterangan) return [];
        $decoded = json_decode($keterangan, true);

        return array_values(array_filter(is_array($decoded) ? $decoded : [$keterangan], fn ($p) => is_string($p) && str_starts_with($p, '/')));
    }

    /**
     * URL buka dokumen per path: lewat viewer One-Procedure bila path milik dokumen prosedur
     * (mendukung file yang masih di folder v1), selain itu langsung ke file publik.
     */
    public function documentUrls(array $paths): array
    {
        $idsByPath = ProsedurKerja::whereIn('file_dokumen', $paths)->pluck('id', 'file_dokumen');

        return array_map(fn ($path) => isset($idsByPath[$path]) ? "/one-procedure/prosedur/view/{$idsByPath[$path]}" : asset(ltrim($path, '/')), $paths);
    }

    private function persist($model, array $attributes, array $data)
    {
        $model->fill($attributes);
        if ($model->exists) $model->aktif = (bool) ($data['aktif'] ?? true);
        $model->save();

        return $model;
    }
}
