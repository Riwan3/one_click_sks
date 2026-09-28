<?php

namespace App\Services;

use App\Imports\CoachingCounselingImport;
use App\Models\CoachingCounseling;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class CoachingCounselingService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage, ?\App\Models\Employee $gl = null, ?\App\Models\User $user = null): LengthAwarePaginator
    {
        $isGlOnly = $user && $user->isGroupLeader() && ! $user->isAdmin();

        return CoachingCounseling::query()
            ->with(['employee', 'coach', 'creator'])
            ->when($isGlOnly, function ($q) use ($gl, $user) {
                $q->where(function ($sub) use ($gl, $user) {
                    if ($gl) {
                        $sub->where('coach_employee_id', $gl->id)
                            ->orWhereHas('employee', fn ($eq) => $eq->where('gl_id', $gl->id));
                    }
                    $sub->orWhere('created_by', $user->id);
                });
            })
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): CoachingCounseling
    {
        if (isset($data['file_bukti']) && $data['file_bukti'] instanceof UploadedFile) {
            $data['file_bukti'] = $data['file_bukti']->store('coaching_counselings', 'public');
        } else {
            unset($data['file_bukti']);
        }

        foreach (['materi_pribadi', 'materi_keluarga', 'materi_lingkungan', 'materi_pekerjaan'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = $data[$field] ? '1' : '0';
            }
        }

        $data['nomor_surat'] = CoachingCounseling::nextNomorSurat($data['tanggal']);
        $data['created_by'] = $createdBy;

        return CoachingCounseling::create($data)->fresh();
    }

    public function update(CoachingCounseling $coachingCounseling, array $data, ?int $updatedBy): CoachingCounseling
    {
        if (isset($data['file_bukti']) && $data['file_bukti'] instanceof UploadedFile) {
            $data['file_bukti'] = $data['file_bukti']->store('coaching_counselings', 'public');
        } else {
            unset($data['file_bukti']);
        }

        foreach (['materi_pribadi', 'materi_keluarga', 'materi_lingkungan', 'materi_pekerjaan'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = $data[$field] ? '1' : '0';
            }
        }

        $data['updated_by'] = $updatedBy;
        $coachingCounseling->update($data);

        return $coachingCounseling;
    }

    public function delete(CoachingCounseling $coachingCounseling): void
    {
        $coachingCounseling->delete();
    }

    public function importFromExcel(UploadedFile $file, ?int $createdBy): int
    {
        $import = new CoachingCounselingImport($createdBy);
        Excel::import($import, $file);

        return $import->importedCount();
    }
}
