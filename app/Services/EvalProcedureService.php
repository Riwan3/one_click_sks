<?php

namespace App\Services;

use App\Models\Procedure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

/** Materi prosedur di tab Procedure Evaluasi Prosedur (padanan EvaluasiProsedurRepository v1). */
class EvalProcedureService
{
    private const UPLOAD_DIR = 'assets/uploads/eval_prosedur';

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    public function autoExpire(): void
    {
        Procedure::whereNotNull('expired_date')
            ->whereDate('expired_date', '<', now()->toDateString())
            ->where('status', '!=', 'expired')
            ->update(['status' => 'expired']);
    }

    public function list(array $filters): Collection
    {
        return Procedure::query()->filter($filters)->withEvaluationCounts()->latest()->get();
    }

    public function categories(): Collection
    {
        return Procedure::whereNotNull('category')->where('category', '!=', '')
            ->distinct()->orderBy('category')->pluck('category');
    }

    public function create(array $data, ?UploadedFile $file, string $createdBy): Procedure
    {
        return Procedure::create([...$this->attributes($data), ...$this->storeFile($file), 'created_by' => $createdBy]);
    }

    public function update(Procedure $procedure, array $data, ?UploadedFile $file): Procedure
    {
        $fileAttributes = $this->storeFile($file);
        if ($fileAttributes) $this->deleteFile($procedure);

        $procedure->update([...$this->attributes($data), ...$fileAttributes]);

        return $procedure;
    }

    public function delete(Procedure $procedure): void
    {
        $this->deleteFile($procedure);
        $procedure->delete();
    }

    private function attributes(array $data): array
    {
        return [
            'title' => trim($data['title']),
            'category' => trim((string) ($data['category'] ?? '')),
            'revision' => trim((string) ($data['revision'] ?? '')),
            'description' => $data['description'] ?? null,
            'risk_level' => $data['risk_level'] ?? 'medium',
            'status' => $data['status'] ?? 'active',
            'effective_date' => $data['effective_date'] ?? null,
            'expired_date' => $data['expired_date'] ?? null,
        ];
    }

    private function storeFile(?UploadedFile $file): array
    {
        if (! $file) return [];

        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'ep_'.uniqid().'.'.$ext;
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

    private function deleteFile(Procedure $procedure): void
    {
        if (! $procedure->file_path) return;

        $path = public_path(ltrim($procedure->file_path, '/'));
        if (is_file($path)) unlink($path);
    }
}
