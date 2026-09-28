<?php

namespace App\Imports;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use App\Models\FatigueInterview;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FatigueInterviewImport implements ToCollection, WithHeadingRow
{
    private int $imported = 0;

    private int $invalidDates = 0;

    public function __construct(private readonly ?int $createdBy) {}

    public function collection(Collection $rows): void
    {
        $siteCode = Setting::getValue('fatigue_interview_site_code', 'ADW') ?: 'ADW';
        $monthRomans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $ranksByYear = [];

        foreach ($rows as $row) {
            $nrp = trim((string) ($row['nrp'] ?? $row['nrp_operator'] ?? ''));

            if ($nrp === '') {
                continue;
            }

            $nama = trim((string) ($row['nama_operator'] ?? $row['nama_karyawan'] ?? $row['nama'] ?? '')) ?: $nrp;
            $jabatan = trim((string) ($row['jabatan'] ?? '')) ?: null;

            $employee = Employee::firstOrCreate(
                ['nrp' => $nrp],
                [
                    'name' => $nama,
                    'jabatan_ss6' => $jabatan,
                    'status_karyawan' => EmployeeStatus::Inactive,
                ]
            );

            $tanggal = $this->parseDate($row['tanggal'] ?? $row['tgl'] ?? null);
            if (! $tanggal) {
                $tanggal = now()->toDateString();
                $this->invalidDates++;
            }

            $year = date('Y', strtotime($tanggal));
            $month = (int) date('n', strtotime($tanggal));

            if (! isset($ranksByYear[$year])) {
                $ranksByYear[$year] = (int) FatigueInterview::query()->whereYear('tanggal', $year)->max('doc_rank');
            }
            $ranksByYear[$year]++;
            $docRank = $ranksByYear[$year];
            $noSurat = sprintf('%03d', $docRank).'/FI/PPA/'.$siteCode.'/PRO/'.$monthRomans[$month - 1].'/'.$year;

            $pengawasId = $this->resolveSupervisor($row['pengawas'] ?? $row['nrp_pengawas'] ?? $row['nama_pengawas'] ?? null);

            FatigueInterview::create([
                'doc_rank' => $docRank,
                'no_surat' => $noSurat,
                'employee_id' => $employee->id,
                'nomor_unit' => $this->cleanString($row['nomor_unit'] ?? $row['unit'] ?? $row['no_unit'] ?? null),
                'area' => $this->cleanString($row['area'] ?? $row['lokasi'] ?? null),
                'pengawas_id' => $pengawasId,
                'tanggal' => $tanggal,
                'jam' => $this->parseTime($row['jam'] ?? $row['waktu'] ?? null),
                'q1_shift_day' => $this->cleanString($row['q1_shift_day'] ?? $row['shift_day'] ?? $row['q1'] ?? null),
                'q2_body_condition' => $this->cleanString($row['q2_body_condition'] ?? $row['kondisi_tubuh'] ?? $row['q2'] ?? null),
                'q3_reason' => $this->cleanString($row['q3_reason'] ?? $row['alasan'] ?? $row['q3'] ?? null),
                'q3_background' => $this->cleanString($row['q3_background'] ?? $row['latar_belakang'] ?? null),
                'q4_rest_hours' => $this->cleanString($row['q4_rest_hours'] ?? $row['jam_istirahat'] ?? $row['q4'] ?? null),
                'q5_rest_quality' => $this->cleanString($row['q5_rest_quality'] ?? $row['kualitas_istirahat'] ?? $row['q5'] ?? null),
                'q5_bad_quality_reason' => $this->cleanString($row['q5_bad_quality_reason'] ?? $row['penyebab_kurang_kualitas'] ?? null),
                'q6_home_comfort' => $this->cleanString($row['q6_home_comfort'] ?? $row['kenyamanan_tempat_tinggal'] ?? $row['q6'] ?? null),
                'q6_discomfort_reason' => $this->cleanString($row['q6_discomfort_reason'] ?? $row['penyebab_tidak_nyaman'] ?? null),
                'q7_other_activities' => $this->cleanString($row['q7_other_activities'] ?? $row['aktivitas_lain'] ?? $row['q7'] ?? null),
                'q7_activity_details' => $this->cleanString($row['q7_activity_details'] ?? $row['detail_aktivitas'] ?? null),
                'q8_education' => $this->cleanString($row['q8_education'] ?? $row['edukasi'] ?? $row['q8'] ?? null),
                'q9_heavy_fatigue_day' => $this->cleanString($row['q9_heavy_fatigue_day'] ?? $row['hari_fatigue_berat'] ?? $row['q9'] ?? null),
                'q10_fit_plan' => $this->cleanString($row['q10_fit_plan'] ?? $row['rencana_fit'] ?? $row['q10'] ?? null),
                'q11_suggestions' => $this->cleanString($row['q11_suggestions'] ?? $row['saran'] ?? $row['q11'] ?? null),
                'created_by' => $this->createdBy,
            ]);

            $this->imported++;
        }
    }

    public function importedCount(): int
    {
        return $this->imported;
    }

    public function invalidDateCount(): int
    {
        return $this->invalidDates;
    }

    private function resolveSupervisor(mixed $value): ?int
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $employee = Employee::where('nrp', $value)->orWhere('name', $value)->first();

        return $employee?->id;
    }

    private function parseDate(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseTime(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->format('H:i:s');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse((string) $value)->format('H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private function cleanString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $str = trim((string) $value);

        return $str !== '' ? $str : null;
    }
}
