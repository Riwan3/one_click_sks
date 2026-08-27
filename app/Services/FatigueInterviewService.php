<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\FatigueInterview;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class FatigueInterviewService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        $user = Auth::user();

        return FatigueInterview::query()
            ->with(['employee', 'pengawas'])
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            // Row-level scoping: GL non-Manpower hanya lihat records mereka handle (sebagai pengawas)
            ->when(
                $user && !$user->isAdmin() && !$user->isPimpinan() && !$user->isGlManpower(),
                fn ($q) => $q->where('pengawas_id', $user->employee_id)
            )
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): FatigueInterview
    {
        [$docRank, $noSurat] = FatigueInterview::nextDocRank($data['tanggal']);

        $data['doc_rank'] = $docRank;
        $data['no_surat'] = $noSurat;
        $data['created_by'] = $createdBy;

        // Snapshot operator & pengawas nama & NRP untuk denormalisasi V1
        // NOTE: Hanya assign jika kolom sudah ada di database (migration future task)
        $model = FatigueInterview::make();
        $fillable = $model->getFillable();

        if (isset($data['employee_id']) && in_array('nrp_operator', $fillable)) {
            $employee = Employee::find($data['employee_id']);
            if ($employee) {
                $data['nrp_operator'] = $employee->nrp;
                $data['nama_operator'] = $employee->name;
            }
        }

        if (isset($data['pengawas_id']) && in_array('nrp_pengawas', $fillable)) {
            $pengawas = Employee::find($data['pengawas_id']);
            if ($pengawas) {
                $data['nrp_pengawas'] = $pengawas->nrp;
                $data['nama_pengawas'] = $pengawas->name;
            }
        }

        return FatigueInterview::create($data)->fresh();
    }

    public function update(FatigueInterview $fatigueInterview, array $data, ?int $updatedBy): FatigueInterview
    {
        $data['updated_by'] = $updatedBy;
        $fatigueInterview->update($data);

        return $fatigueInterview;
    }

    public function delete(FatigueInterview $fatigueInterview): void
    {
        $fatigueInterview->delete();
    }
}
