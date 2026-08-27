<?php

namespace App\Services;

use App\Models\CoachingMateri;
use App\Models\CoachingMateriRevisi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CoachingMateriService
{
    public function create(array $data, int $createdBy): CoachingMateri
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $materi = CoachingMateri::create($data);
            $this->storeFiles($materi, $data);

            return $materi->fresh();
        });
    }

    public function update(CoachingMateri $materi, array $data, int $createdBy, bool $isRevisi = false): CoachingMateri
    {
        return DB::transaction(function () use ($materi, $data, $createdBy, $isRevisi) {
            if ($isRevisi) {
                // Simpan versi lama sebagai revisi
                $this->createRevision($materi, $createdBy);

                // Update versi
                $materi->update(['versi' => $materi->versi + 1]);
            }

            $materi->update($data);
            $this->storeFiles($materi, $data);

            return $materi->fresh();
        });
    }

    private function storeFiles(CoachingMateri $materi, array $data): void
    {
        if (isset($data['file_pdf']) && $data['file_pdf'] instanceof \Illuminate\Http\UploadedFile) {
            if ($materi->file_pdf) {
                Storage::disk('public')->delete($materi->file_pdf);
            }
            $materi->update(['file_pdf' => $data['file_pdf']->store('coaching-pdf', 'public')]);
        }

        if (isset($data['video']) && $data['video'] instanceof \Illuminate\Http\UploadedFile) {
            if ($materi->video) {
                Storage::disk('public')->delete($materi->video);
            }
            $materi->update(['video' => $data['video']->store('coaching-video', 'public')]);
        }

        if (isset($data['thumbnail']) && $data['thumbnail'] instanceof \Illuminate\Http\UploadedFile) {
            if ($materi->thumbnail) {
                Storage::disk('public')->delete($materi->thumbnail);
            }
            $materi->update(['thumbnail' => $data['thumbnail']->store('coaching-thumbnail', 'public')]);
        }
    }

    private function createRevision(CoachingMateri $materi, int $createdBy): void
    {
        CoachingMateriRevisi::create([
            'materi_id' => $materi->id,
            'versi' => $materi->versi,
            'judul' => $materi->judul,
            'ringkasan' => $materi->ringkasan,
            'deskripsi' => $materi->deskripsi,
            'file_pdf' => $materi->file_pdf,
            'video' => $materi->video,
            'link_sop' => $materi->link_sop,
            'thumbnail' => $materi->thumbnail,
            'created_by' => $createdBy,
        ]);
    }
}
