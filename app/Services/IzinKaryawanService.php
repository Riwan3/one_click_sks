<?php

namespace App\Services;

use App\Enums\IzinJenis;
use App\Enums\IzinStatus;
use App\Models\Employee;
use App\Models\IzinKaryawan;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class IzinKaryawanService
{
    private const UPLOAD_DIR = 'izin-karyawan';

    private const FIELDS_BY_JENIS = [
        'izin' => ['alasan', 'tujuan', 'alamat', 'tgl_berangkat', 'tgl_kembali'],
        'emergency' => ['alasan', 'tgl_berangkat', 'tgl_kembali'],
        'sakit' => [
            'no_telepon', 'keluhan', 'hasil_td', 'hasil_rr', 'hasil_t', 'hasil_p', 'hasil_spo',
            'diagnosa', 'terapi', 'keterangan_status', 'rujuk_ke', 'rumah_sakit_rujukan',
            'dokter_rujukan', 'dipulangkan_tidur',
        ],
    ];

    public function paginate(array $filters, int $page, int $perPage): LengthAwarePaginator
    {
        return IzinKaryawan::query()
            ->with('approver')
            ->jenis($filters['jenis_izin'] ?? null)
            ->status($filters['status'] ?? null)
            ->search($filters['search'] ?? null)
            ->createdByUser($filters['created_by'] ?? null)
            ->dateBetween($filters['date_from'] ?? null, $filters['date_to'] ?? null)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * All rows matching the list filters, unpaginated — backs the admin-only
     * CSV export (mirrors V1's export() calling repo->getAll(10000, ...)).
     */
    public function allForExport(array $filters): \Illuminate\Support\Collection
    {
        return IzinKaryawan::query()
            ->jenis($filters['jenis_izin'] ?? null)
            ->status($filters['status'] ?? null)
            ->search($filters['search'] ?? null)
            ->createdByUser($filters['created_by'] ?? null)
            ->dateBetween($filters['date_from'] ?? null, $filters['date_to'] ?? null)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function create(array $data, ?UploadedFile $bukti, ?int $createdBy): IzinKaryawan
    {
        $jenis = IzinJenis::from($data['jenis_izin']);
        $payload = $this->filterFieldsForJenis($data, $jenis);
        $payload['no_surat'] = IzinKaryawan::nextSuratNumber($jenis);
        $payload['created_by'] = $createdBy;

        if ($bukti) {
            $payload['file_bukti'] = $bukti->store(self::UPLOAD_DIR, 'public');
        }

        return IzinKaryawan::create($payload);
    }

    public function update(IzinKaryawan $izin, array $data, ?UploadedFile $bukti): IzinKaryawan
    {
        $jenis = IzinJenis::from($data['jenis_izin']);
        $payload = $this->filterFieldsForJenis($data, $jenis);

        if ($bukti) {
            if ($izin->file_bukti) {
                Storage::disk('public')->delete($izin->file_bukti);
            }
            $payload['file_bukti'] = $bukti->store(self::UPLOAD_DIR, 'public');
        }

        $izin->update($payload);

        return $izin;
    }

    public function delete(IzinKaryawan $izin): void
    {
        if ($izin->file_bukti) {
            Storage::disk('public')->delete($izin->file_bukti);
        }

        $izin->delete();
    }

    public function approve(IzinKaryawan $izin, ?int $approvedBy): IzinKaryawan
    {
        $izin->update(['status' => IzinStatus::Approved->value, 'approved_by' => $approvedBy, 'approved_at' => now()]);

        return $izin;
    }

    public function decline(IzinKaryawan $izin, ?int $approvedBy): IzinKaryawan
    {
        $izin->update(['status' => IzinStatus::Declined->value, 'approved_by' => $approvedBy, 'approved_at' => now()]);

        return $izin;
    }

    public function reset(IzinKaryawan $izin): IzinKaryawan
    {
        $izin->update(['status' => IzinStatus::Awaiting->value, 'approved_by' => null, 'approved_at' => null]);

        return $izin;
    }

    public function cancel(IzinKaryawan $izin): IzinKaryawan
    {
        $izin->update(['status' => IzinStatus::Cancelled->value]);

        return $izin;
    }

    public function searchEmployee(string $keyword): \Illuminate\Support\Collection
    {
        return Employee::query()
            ->search($keyword)
            ->limit(10)
            ->get(['nrp', 'name', 'jabatan_ss6', 'unit'])
            ->map(fn (Employee $employee) => [
                'nrp' => $employee->nrp,
                'nama' => $employee->name,
                'jabatan' => $employee->jabatan_ss6,
                'departemen' => $employee->unit,
            ]);
    }

    private function filterFieldsForJenis(array $data, IzinJenis $jenis): array
    {
        $common = ['jenis_izin', 'nama', 'nrp', 'jabatan', 'departemen'];
        $allowed = array_merge($common, self::FIELDS_BY_JENIS[$jenis->value]);
        $allFields = array_merge($common, ...array_values(self::FIELDS_BY_JENIS));

        $payload = array_fill_keys(array_diff($allFields, $allowed), null);
        $payload = array_merge($payload, array_intersect_key($data, array_flip($allowed)));
        $payload['jenis_izin'] = $jenis->value;

        return $payload;
    }
}
