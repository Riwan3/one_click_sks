<?php

namespace App\Services;

use App\Models\AcademyMateri;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

/** Materi training di tab Materi Evaluasi Kompetensi (padanan AcademyMateriRepository v1). */
class AcademyMateriService
{
    private const UPLOAD_DIR = 'assets/uploads/academy_materi';

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    public function autoExpire(): void
    {
        AcademyMateri::whereNotNull('expired_date')
            ->whereDate('expired_date', '<', now()->toDateString())
            ->where('status', '!=', 'expired')
            ->update(['status' => 'expired']);
    }

    public function list(array $filters): Collection
    {
        return AcademyMateri::query()->filter($filters)->withEvaluationCounts()->latest()->get();
    }

    public function categories(): Collection
    {
        return AcademyMateri::whereNotNull('category')->where('category', '!=', '')
            ->distinct()->orderBy('category')->pluck('category');
    }

    public function create(array $data, ?UploadedFile $file, string $createdBy): AcademyMateri
    {
        return AcademyMateri::create([...$this->attributes($data), ...$this->storeFile($file), 'created_by' => $createdBy]);
    }

    public function update(AcademyMateri $materi, array $data, ?UploadedFile $file): AcademyMateri
    {
        $fileAttributes = $this->storeFile($file);
        if ($fileAttributes) {
            $this->deleteFile($materi);
        }

        $materi->update([...$this->attributes($data), ...$fileAttributes]);

        return $materi;
    }

    public function delete(AcademyMateri $materi): void
    {
        $this->deleteFile($materi);
        $materi->delete();
    }

    private function attributes(array $data): array
    {
        return [
            'title' => trim($data['title']),
            'category' => trim((string) ($data['category'] ?? '')),
            'revision' => trim((string) ($data['revision'] ?? '')) ?: 'Rev. 0',
            'description' => $data['description'] ?? null,
            'risk_level' => $data['risk_level'] ?? 'medium',
            'status' => $data['status'] ?? 'active',
            'effective_date' => $data['effective_date'] ?? null,
            'expired_date' => $data['expired_date'] ?? null,
        ];
    }

    private function storeFile(?UploadedFile $file): array
    {
        if (! $file) {
            return [];
        }

        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'am_'.uniqid().'.'.$ext;
        $file->move(public_path(self::UPLOAD_DIR), $filename);

        return [
            'file_path' => '/'.self::UPLOAD_DIR.'/'.$filename,
            'file_type' => match (true) {
                in_array($ext, self::IMAGE_EXTENSIONS, true) => 'image',
                $ext === 'docx' => 'docx',
                default => 'pdf',
            },
        ];
    }

    private function deleteFile(AcademyMateri $materi): void
    {
        if (! $materi->file_path) {
            return;
        }

        $path = public_path(ltrim($materi->file_path, '/'));
        if (is_file($path)) {
            unlink($path);
        }
    }
}
