<?php

namespace App\Services;

use App\Models\OpdMinimalGrade;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Beranda OPD: memantau tiap karyawan Monitoring Grade & Jabatan terhadap matriks Minimal Grade.
 *  1. Under grade   : grade saat ini < GRADE MIN posisi.
 *  2. Grade sesuai  : grade saat ini ada di antara GRADE MIN dan MAX posisi.
 *  3. Lama di grade : sudah >= 3 tahun di grade saat ini.
 *  4. Lama di posisi: dasar penentuan training ODP.
 * Urutan grade: 1B < 1C < ... < 1F < 2A < 2B ...
 */
class BerandaOpdService
{
    private const MIN_YEARS_IN_GRADE = 3.0;

    private const SEVERITY_ORDER = ['under' => 0, 'high' => 1, 'ok' => 2, 'na' => 3];

    private const MONTHS = [
        'januari' => 1, 'jan' => 1, 'februari' => 2, 'feb' => 2, 'pebruari' => 2, 'maret' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4, 'mei' => 5, 'juni' => 6, 'jun' => 6, 'juli' => 7, 'jul' => 7,
        'agustus' => 8, 'agu' => 8, 'agt' => 8, 'ags' => 8, 'aug' => 8, 'september' => 9, 'sep' => 9, 'sept' => 9,
        'oktober' => 10, 'okt' => 10, 'oct' => 10, 'november' => 11, 'nov' => 11, 'desember' => 12, 'des' => 12, 'dec' => 12,
    ];

    public function __construct(private PengajuanGradeService $pengajuan) {}

    /** @return array{rows: Collection, hasEmployeeData: bool, hasMatrix: bool, year: int} */
    public function build(): array
    {
        $year = (int) date('Y');
        $employees = DB::table('odp_mg_grade_v4')
            ->select('nrp', 'nama', 'posisi', 'grade_now', 'efektif_grade', 'efektif_position', 'masa_kerja_grade_sekarang', 'masa_kerja_posisi_sekarang')
            ->orderBy('nama')->orderBy('nrp')->get();
        $matrix = $this->gradeMatrix();
        $used = $this->pengajuan->usedTermins($year);

        $rows = $employees
            ->map(fn ($e) => $this->evaluate($e, $matrix, $used[trim((string) $e->nrp)] ?? []))
            ->sort(fn ($a, $b) => [self::SEVERITY_ORDER[$a['status']], $a['name']] <=> [self::SEVERITY_ORDER[$b['status']], $b['name']])
            ->values();

        return ['rows' => $rows, 'hasEmployeeData' => $employees->isNotEmpty(), 'hasMatrix' => $matrix !== [], 'year' => $year];
    }

    /** @return array<string, array{min: ?int, max: ?int, label: string}> Unit ternormalisasi => rentang grade (baris pertama per unit). */
    private function gradeMatrix(): array
    {
        $matrix = [];
        foreach (OpdMinimalGrade::orderBy('id')->get(['unit', 'grade_min_max', 'grade_min']) as $row) {
            $key = $this->normalizeUnit($row->unit);
            if ($key === '' || isset($matrix[$key])) {
                continue;
            }

            $parts = array_values(array_filter(
                preg_split('/\s*[-–—]\s*|\s+s\/d\s+/i', trim((string) $row->grade_min_max)),
                fn ($p) => trim($p) !== '',
            ));
            $min = trim((string) $row->grade_min) ?: trim($parts[0] ?? '');
            $max = trim(end($parts) ?: $min);
            $matrix[$key] = [
                'min' => $this->gradeRank($min),
                'max' => $this->gradeRank($max),
                'label' => $min === $max ? $min : "{$min} - {$max}",
            ];
        }

        return $matrix;
    }

    private function evaluate(object $employee, array $matrix, array $usedTermins): array
    {
        $grade = trim((string) $employee->grade_now);
        $limit = $matrix[$this->normalizeUnit($employee->posisi)] ?? null;
        $rank = $this->gradeRank($grade);
        $yearsInGrade = $this->yearsSince($employee->efektif_grade, $employee->masa_kerja_grade_sekarang);

        return [
            'nrp' => trim((string) $employee->nrp),
            'name' => trim((string) $employee->nama),
            'position' => trim((string) $employee->posisi),
            'grade' => $grade,
            'gradeRank' => $rank ?? 0,
            'gradeRange' => $limit['label'] ?? '',
            'status' => $this->status($rank, $limit),
            'yearsInGrade' => $yearsInGrade,
            'yearsInPosition' => $this->yearsSince($employee->efektif_position, $employee->masa_kerja_posisi_sekarang),
            'isOverMinYears' => $yearsInGrade !== null && $yearsInGrade >= self::MIN_YEARS_IN_GRADE,
            'minYears' => self::MIN_YEARS_IN_GRADE,
            'usedTermins' => $usedTermins,
        ];
    }

    /** under | high | ok | na */
    private function status(?int $rank, ?array $limit): string
    {
        if ($rank === null || ($limit['min'] ?? null) === null) {
            return 'na';
        }
        if ($rank < $limit['min']) {
            return 'under';
        }

        return $limit['max'] !== null && $rank > $limit['max'] ? 'high' : 'ok';
    }

    /** "OPERATOR HD 785" / "hd785" => "HD785". */
    private function normalizeUnit(?string $unit): string
    {
        return preg_replace('/[^A-Z0-9]/', '', preg_replace('/^OPERATOR\s+/', '', strtoupper(trim((string) $unit))));
    }

    /** "1F" => 106, "2A" => 201 (level * 100 + urutan huruf). */
    private function gradeRank(?string $grade): ?int
    {
        if (! preg_match('/^\s*(\d+)\s*([A-Za-z])\s*$/', (string) $grade, $m)) {
            return null;
        }

        return ((int) $m[1]) * 100 + (ord(strtoupper($m[2])) - 64);
    }

    /** Lama (tahun, pecahan) dari tanggal naik; bila tanggal tak terbaca, dari teks lama ("4,4 Tahun", "3 tahun 2 bulan", "26 bulan", "4"). */
    private function yearsSince(?string $date, ?string $text): ?float
    {
        $parsed = $this->parseDate($date);
        if ($parsed) {
            return max(0.0, (now()->timestamp - $parsed->timestamp) / 86400 / 365.25);
        }

        return $this->yearsFromText($text);
    }

    private function yearsFromText(?string $text): ?float
    {
        $text = trim((string) $text);
        if ($text === '') {
            return null;
        }
        if (preg_match('/^\d+(?:[.,]\d+)?$/', $text)) {
            return (float) str_replace(',', '.', $text);
        }

        $hasYears = preg_match('/(\d+(?:[.,]\d+)?)\s*(?:tahun|thn|th|year|yr)/i', $text, $years);
        $hasMonths = preg_match('/(\d+(?:[.,]\d+)?)\s*(?:bulan|bln|bl|month)/i', $text, $months);
        if (! $hasYears && ! $hasMonths) {
            return null;
        }

        return ($hasYears ? (float) str_replace(',', '.', $years[1]) : 0.0)
            + ($hasMonths ? (float) str_replace(',', '.', $months[1]) / 12 : 0.0);
    }

    /** Dikenali: dd/mm/yyyy, yyyy-mm-dd, "1-Des-24", "3 Mei 2013" (bulan Indonesia). */
    private function parseDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (preg_match('#^(\d{1,2})[/.\-](\d{1,2})[/.\-](\d{4})$#', $value, $m)) {
            return $this->makeDate($m[3], $m[2], $m[1]);
        }
        if (preg_match('#^(\d{4})[/.\-](\d{1,2})[/.\-](\d{1,2})#', $value, $m)) {
            return $this->makeDate($m[1], $m[2], $m[3]);
        }
        if (preg_match('/^(\d{1,2})[\s.\-\/]+([A-Za-z]+)[\s.\-\/,]+(\d{2}|\d{4})$/', $value, $m) && isset(self::MONTHS[strtolower($m[2])])) {
            $year = (int) $m[3];
            $year += $year >= 100 ? 0 : ($year < 70 ? 2000 : 1900);

            return $this->makeDate($year, self::MONTHS[strtolower($m[2])], $m[1]);
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function makeDate(int|string $year, int|string $month, int|string $day): ?Carbon
    {
        return checkdate((int) $month, (int) $day, (int) $year) ? Carbon::create((int) $year, (int) $month, (int) $day) : null;
    }
}
