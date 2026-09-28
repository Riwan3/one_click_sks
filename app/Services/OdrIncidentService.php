<?php

namespace App\Services;

use App\Enums\OdrIncidentStatus;
use App\Models\OdrIncident;
use App\Models\OdrIncidentAction;
use App\Models\OdrIncidentEvidence;
use App\Models\OdrMasterArea;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** Perbaikan Pasca-Insiden One-Reporting (padanan database/odr/perbaikan_pasca_insiden.php v1). */
class OdrIncidentService
{
    public const EVIDENCE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];

    private const EVIDENCE_DIR = 'perbaikan_pasca_insiden';

    public function normalizeFilters(array $input): array
    {
        $text = fn ($key) => trim((string) ($input[$key] ?? ''));

        return [
            'q' => $text('q'),
            'status' => OdrIncidentStatus::tryFrom($text('status'))?->value ?? '',
            'area' => $text('area'),
        ];
    }

    public function list(array $filters): Collection
    {
        return OdrIncident::query()
            ->with('actions.evidences')
            ->when($filters['status'], fn ($q, $v) => $q->where('status', $v))
            ->when($filters['area'], fn ($q, $v) => $q->where('area', $v))
            ->when($filters['q'], fn ($q, $v) => $q->where('judul_insiden', 'like', "%{$v}%"))
            ->orderByDesc('tanggal_insiden')
            ->orderByDesc('id')
            ->get();
    }

    /** KPI selalu dihitung dari seluruh data (tidak ikut filter), sama seperti v1. */
    public function kpi(): array
    {
        $counts = OdrIncident::selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');

        return [
            'total' => (int) $counts->sum(),
            'open' => (int) ($counts[OdrIncidentStatus::Open->value] ?? 0),
            'progress' => (int) ($counts[OdrIncidentStatus::Progress->value] ?? 0),
            'selesai' => (int) ($counts[OdrIncidentStatus::Selesai->value] ?? 0),
            'overdue' => OdrIncident::overdue()->count(),
        ];
    }

    /** Area dari master area aktif, fallback ke area yang sudah tercatat. */
    public function areaOptions(): Collection
    {
        $areas = OdrMasterArea::where('aktif', true)->orderBy('nama')->pluck('nama');

        return $areas->isNotEmpty() ? $areas
            : OdrIncident::whereNotNull('area')->where('area', '!=', '')->distinct()->orderBy('area')->pluck('area');
    }

    public function create(array $data): OdrIncident
    {
        return OdrIncident::create([...$data, 'status' => OdrIncidentStatus::Open]);
    }

    public function updateStatus(OdrIncident $incident, array $data): void
    {
        $incident->update(['status' => $data['status'], 'target_selesai' => $data['target_selesai'] ?? null]);
    }

    public function delete(OdrIncident $incident): void
    {
        $paths = OdrIncidentEvidence::whereIn('tindakan_id', $incident->actions()->pluck('id'))->pluck('file_path');

        DB::transaction(fn () => $incident->delete());
        $this->deleteFiles($paths);
    }

    /** @param  UploadedFile[]  $files */
    public function addAction(OdrIncident $incident, array $data, array $files, string $uploadedBy): OdrIncidentAction
    {
        $action = $incident->actions()->create($data);
        $this->storeEvidence($action, $files, $uploadedBy);

        return $action;
    }

    public function deleteAction(OdrIncidentAction $action): void
    {
        $paths = $action->evidences()->pluck('file_path');

        $action->delete();
        $this->deleteFiles($paths);
    }

    /**
     * Simpan file evidence (ekstensi di luar daftar diabaikan, sama seperti v1).
     *
     * @param  UploadedFile[]  $files
     */
    public function storeEvidence(OdrIncidentAction $action, array $files, string $uploadedBy): int
    {
        $allowed = array_filter($files, fn (UploadedFile $f) => in_array(strtolower($f->getClientOriginalExtension()), self::EVIDENCE_EXTENSIONS, true));

        foreach ($allowed as $file) {
            $action->evidences()->create([
                'file_path' => $file->store(self::EVIDENCE_DIR, 'public'),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by' => $uploadedBy,
            ]);
        }

        return count($allowed);
    }

    public function deleteEvidence(OdrIncidentEvidence $evidence): void
    {
        $evidence->delete();
        $this->deleteFiles(collect([$evidence->file_path]));
    }

    private function deleteFiles(Collection $paths): void
    {
        if ($paths->isNotEmpty()) Storage::disk('public')->delete($paths->all());
    }
}
