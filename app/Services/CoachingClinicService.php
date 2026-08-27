<?php

namespace App\Services;

use App\Models\CoachingClinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CoachingClinicService
{
    public function paginate(int $perPage = 15)
    {
        return CoachingClinic::query()
            ->with(['employee', 'coach', 'creator'])
            ->latest('tanggal')
            ->paginate($perPage);
    }

    public function create(array $data): CoachingClinic
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['file_bukti']) && $data['file_bukti'] instanceof \Illuminate\Http\UploadedFile) {
                $data['file_bukti'] = $data['file_bukti']->store('coaching-bukti', 'public');
            }

            return CoachingClinic::create($data);
        });
    }

    public function update(CoachingClinic $clinic, array $data): CoachingClinic
    {
        return DB::transaction(function () use ($clinic, $data) {
            if (isset($data['file_bukti']) && $data['file_bukti'] instanceof \Illuminate\Http\UploadedFile) {
                if ($clinic->file_bukti) {
                    Storage::disk('public')->delete($clinic->file_bukti);
                }
                $data['file_bukti'] = $data['file_bukti']->store('coaching-bukti', 'public');
            }

            $clinic->update($data);
            return $clinic->fresh(['employee', 'coach', 'creator']);
        });
    }

    public function delete(CoachingClinic $clinic): bool
    {
        return DB::transaction(function () use ($clinic) {
            if ($clinic->file_bukti) {
                Storage::disk('public')->delete($clinic->file_bukti);
            }
            return $clinic->delete();
        });
    }
}
