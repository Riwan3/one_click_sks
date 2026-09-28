<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PemanggilanKaryawan;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class PemanggilanKaryawanService
{
    public function paginate(?string $search, ?string $dateFrom, ?string $dateTo, int $page, int $perPage): LengthAwarePaginator
    {
        return PemanggilanKaryawan::query()
            ->with(['employee', 'creator', 'updater'])
            ->search($search)
            ->dateBetween($dateFrom, $dateTo)
            ->orderByDesc('tanggal_pemanggilan')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?int $createdBy): PemanggilanKaryawan
    {
        $data['created_by'] = $createdBy;

        if (empty($data['employee_id']) && ! empty($data['nrp'])) {
            $emp = Employee::where('nrp', $data['nrp'])->first();
            if ($emp) {
                $data['employee_id'] = $emp->id;
            }
        }

        if (empty($data['no_surat'])) {
            $data['no_surat'] = PemanggilanKaryawan::nextNomorSurat($data['tanggal_pemanggilan'] ?? null);
        }

        if (isset($data['eviden_1']) && $data['eviden_1'] instanceof UploadedFile) {
            $data['eviden_1'] = $data['eviden_1']->store('pemanggilan_karyawan', 'public');
        }

        if (isset($data['eviden_2']) && $data['eviden_2'] instanceof UploadedFile) {
            $data['eviden_2'] = $data['eviden_2']->store('pemanggilan_karyawan', 'public');
        }

        if (isset($data['foto']) && $data['foto'] instanceof UploadedFile) {
            $data['foto'] = $data['foto']->store('uploads/pemanggilan_karyawan', 'public');
        }

        return PemanggilanKaryawan::create($data);
    }

    public function update(PemanggilanKaryawan $pemanggilan, array $data, ?int $updatedBy = null): PemanggilanKaryawan
    {
        if ($updatedBy) {
            $data['updated_by'] = $updatedBy;
        }

        if (empty($data['employee_id']) && ! empty($data['nrp'])) {
            $emp = Employee::where('nrp', $data['nrp'])->first();
            if ($emp) {
                $data['employee_id'] = $emp->id;
            }
        }

        if (isset($data['eviden_1'])) {
            if ($data['eviden_1'] instanceof UploadedFile) {
                if ($pemanggilan->eviden_1 && Storage::disk('public')->exists($pemanggilan->eviden_1)) {
                    Storage::disk('public')->delete($pemanggilan->eviden_1);
                }
                $data['eviden_1'] = $data['eviden_1']->store('pemanggilan_karyawan', 'public');
            } elseif ($data['eviden_1'] === null) {
                unset($data['eviden_1']);
            }
        }

        if (isset($data['eviden_2'])) {
            if ($data['eviden_2'] instanceof UploadedFile) {
                if ($pemanggilan->eviden_2 && Storage::disk('public')->exists($pemanggilan->eviden_2)) {
                    Storage::disk('public')->delete($pemanggilan->eviden_2);
                }
                $data['eviden_2'] = $data['eviden_2']->store('pemanggilan_karyawan', 'public');
            } elseif ($data['eviden_2'] === null) {
                unset($data['eviden_2']);
            }
        }

        if (! empty($data['hapus_foto']) && $pemanggilan->foto) {
            if (Storage::disk('public')->exists($pemanggilan->foto)) {
                Storage::disk('public')->delete($pemanggilan->foto);
            }
            $data['foto'] = null;
        } elseif (isset($data['foto']) && $data['foto'] instanceof UploadedFile) {
            if ($pemanggilan->foto && Storage::disk('public')->exists($pemanggilan->foto)) {
                Storage::disk('public')->delete($pemanggilan->foto);
            }
            $data['foto'] = $data['foto']->store('uploads/pemanggilan_karyawan', 'public');
        } else {
            unset($data['foto']);
        }
        unset($data['hapus_foto']);

        $pemanggilan->update($data);

        return $pemanggilan;
    }

    public function delete(PemanggilanKaryawan $pemanggilan): void
    {
        if ($pemanggilan->eviden_1 && Storage::disk('public')->exists($pemanggilan->eviden_1)) {
            Storage::disk('public')->delete($pemanggilan->eviden_1);
        }
        if ($pemanggilan->eviden_2 && Storage::disk('public')->exists($pemanggilan->eviden_2)) {
            Storage::disk('public')->delete($pemanggilan->eviden_2);
        }
        if ($pemanggilan->foto && Storage::disk('public')->exists($pemanggilan->foto)) {
            Storage::disk('public')->delete($pemanggilan->foto);
        }

        $pemanggilan->delete();
    }
}
