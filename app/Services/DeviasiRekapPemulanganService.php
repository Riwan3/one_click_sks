<?php

namespace App\Services;

use App\Models\DeviasiRekapPemulangan;
use App\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;

class DeviasiRekapPemulanganService
{
    public function paginate(array $filters, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $query = DeviasiRekapPemulangan::query()
            ->search($filters['search'] ?? null)
            ->dateRange($filters['date_from'] ?? null, $filters['date_to'] ?? null)
            ->shift($filters['shift'] ?? null)
            ->orderByDesc('tanggal')
            ->orderByDesc('jam')
            ->orderByDesc('id');

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getStats(): array
    {
        $today = now()->toDateString();

        $total = DeviasiRekapPemulangan::count();
        $todayCount = DeviasiRekapPemulangan::whereDate('tanggal', $today)->count();
        $shiftD = DeviasiRekapPemulangan::where('shift', 'D')->count();
        $shiftN = DeviasiRekapPemulangan::where('shift', 'N')->count();

        return [
            'total' => $total,
            'today' => $todayCount,
            'shift_d' => $shiftD,
            'shift_n' => $shiftN,
        ];
    }

    public function create(array $data, ?string $createdBy): DeviasiRekapPemulangan
    {
        if (empty($data['nama']) || empty($data['jabatan'])) {
            $emp = Employee::where('nrp', $data['nrp'])->first();
            if ($emp) {
                $data['nama'] = $data['nama'] ?: $emp->name;
                $data['jabatan'] = $data['jabatan'] ?: $emp->jabatan_ss6;
            }
        }

        if (empty($data['nama'])) {
            $data['nama'] = $data['nrp'];
        }

        $ket = $data['keterangan'] ?? ($data['alasan'] ?? null);
        $data['alasan'] = $ket;
        $data['keterangan'] = $ket;
        $data['created_by'] = $createdBy;

        return DeviasiRekapPemulangan::create($data);
    }

    public function update(DeviasiRekapPemulangan $rekap, array $data, ?string $updatedBy): DeviasiRekapPemulangan
    {
        if (empty($data['nama']) || empty($data['jabatan'])) {
            $emp = Employee::where('nrp', $data['nrp'])->first();
            if ($emp) {
                $data['nama'] = $data['nama'] ?: $emp->name;
                $data['jabatan'] = $data['jabatan'] ?: $emp->jabatan_ss6;
            }
        }

        if (empty($data['nama'])) {
            $data['nama'] = $data['nrp'];
        }

        $ket = $data['keterangan'] ?? ($data['alasan'] ?? null);
        $data['alasan'] = $ket;
        $data['keterangan'] = $ket;
        $data['updated_by'] = $updatedBy;

        $rekap->update($data);

        return $rekap;
    }

    public function delete(DeviasiRekapPemulangan $rekap): void
    {
        $rekap->delete();
    }
}
