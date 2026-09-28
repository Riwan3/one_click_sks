<?php

namespace App\Services;

use App\Enums\ProcedureCertificateStatus;
use App\Models\Procedure;
use App\Models\ProcedureAssessment;
use App\Models\ProcedureAssignment;
use App\Models\ProcedureCertificate;
use App\Models\ProcedureEvaluation;
use App\Models\ProsedurCategory;
use App\Models\ProsedurKerja;
use App\Models\ProsedurType;
use Illuminate\Support\Collection;

/** Ringkasan Dashboard One-Procedure, sumber datanya sama dengan menu Prosedur, Activity & Evaluasi Prosedur. */
class OneProcedureDashboardService
{
    private const RECENT_LIMIT = 10;

    /** Dokumen aktif per jenis (menu Prosedur), termasuk dokumen aktivitas dengan jenis "{kategori}_{jenis}". */
    public function documentSummary(): array
    {
        $types = ProsedurType::orderBy('id')->get()->map(fn (ProsedurType $type) => [
            'name' => trim($type->name),
            'total' => ProsedurKerja::where('status', 'aktif')
                ->where(fn ($q) => $q->where('jenis', $type->slug)->orWhere('jenis', 'like', "%\\_{$type->slug}"))
                ->count(),
            'url' => "/one-procedure/prosedur?type={$type->slug}",
        ]);

        return [
            'total' => ProsedurKerja::count(),
            'active' => ProsedurKerja::where('status', 'aktif')->count(),
            'types' => $types,
        ];
    }

    /** Kategori aktivitas beserta jumlah dokumen yang dipetakan (menu Activity). */
    public function activitySummary(): Collection
    {
        return ProsedurCategory::withCount('documents')->orderBy('id')->get()
            ->map(fn (ProsedurCategory $category) => ['name' => $category->name, 'total' => $category->documents_count]);
    }

    /** Angka utama tiap tab Evaluasi Prosedur. */
    public function evaluationSummary(): array
    {
        return [
            'procedures' => Procedure::count(),
            'assessments' => ProcedureAssessment::count(),
            'published' => ProcedureAssessment::where('status', 'published')->count(),
            'assigned' => ProcedureAssignment::count(),
            'passed' => ProcedureEvaluation::where('status', 'passed')->distinct()->count('nrp'),
            'certificates' => ProcedureCertificate::whereIn('status', [ProcedureCertificateStatus::Active, ProcedureCertificateStatus::ExpiringSoon])->count(),
        ];
    }

    /** Dokumen terbaru (menu Prosedur) dengan label jenis/kategori yang sama seperti tabel Prosedur. */
    public function recentDocuments(string $search): Collection
    {
        $typeNames = ProsedurType::pluck('name', 'slug')->map(fn ($name) => trim($name));
        $categoryNames = ProsedurCategory::pluck('name', 'slug');

        return ProsedurKerja::query()
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('judul', 'like', "%{$search}%")->orWhere('kode_dokumen', 'like', "%{$search}%")))
            ->latest()
            ->take(self::RECENT_LIMIT)
            ->get()
            ->each(fn (ProsedurKerja $doc) => $doc->setAttribute('jenis_label', $this->jenisLabel((string) $doc->jenis, $typeNames, $categoryNames)));
    }

    private function jenisLabel(string $jenis, Collection $typeNames, Collection $categoryNames): string
    {
        $categorySlug = $categoryNames->keys()->first(fn ($slug) => $jenis === $slug || str_starts_with($jenis, "{$slug}_"));
        if (! $categorySlug) return strtoupper($typeNames[$jenis] ?? str_replace('_', ' ', $jenis));

        $typeSlug = substr($jenis, strlen($categorySlug) + 1);

        return strtoupper(trim($categoryNames[$categorySlug].' - '.($typeNames[$typeSlug] ?? $typeSlug), ' -'));
    }
}
