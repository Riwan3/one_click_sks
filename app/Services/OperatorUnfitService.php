<?php

namespace App\Services;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use App\Models\OperatorUnfit;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class OperatorUnfitService
{
    private const UPLOAD_DIR = 'operator-unfit';

    public function paginate(?string $search, int $page, int $perPage): LengthAwarePaginator
    {
        return OperatorUnfit::query()
            ->search($search)
            ->orderByDesc('tgl_mulai_unfit')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?UploadedFile $bukti, ?int $createdBy): OperatorUnfit
    {
        if ($bukti) {
            $data['bukti'] = $bukti->store(self::UPLOAD_DIR, 'public');
        }
        $data['created_by'] = $createdBy;

        $unfit = OperatorUnfit::create($data);
        $this->syncEmployeeStatus($unfit);

        return $unfit;
    }

    public function update(OperatorUnfit $unfit, array $data, ?UploadedFile $bukti): OperatorUnfit
    {
        if ($bukti) {
            if ($unfit->bukti) {
                Storage::disk('public')->delete($unfit->bukti);
            }
            $data['bukti'] = $bukti->store(self::UPLOAD_DIR, 'public');
        }

        $unfit->update($data);
        $this->syncEmployeeStatus($unfit);

        return $unfit;
    }

    public function delete(OperatorUnfit $unfit): void
    {
        if ($unfit->bukti) {
            Storage::disk('public')->delete($unfit->bukti);
        }

        $nrp = $unfit->nrp;
        $unfit->delete();

        Employee::query()->where('nrp', $nrp)->update(['status_karyawan' => EmployeeStatus::Fit->value]);
    }

    public function endUnfit(OperatorUnfit $unfit, ?string $tglAkhir): OperatorUnfit
    {
        $unfit->update(['tgl_akhir_unfit' => $tglAkhir ?: now()->toDateString()]);
        $this->syncEmployeeStatus($unfit);

        return $unfit;
    }

    private function syncEmployeeStatus(OperatorUnfit $unfit): void
    {
        $status = $unfit->fresh()->isActive() ? EmployeeStatus::Unfit : EmployeeStatus::Fit;

        Employee::query()->where('nrp', $unfit->nrp)->update(['status_karyawan' => $status->value]);
    }
}
