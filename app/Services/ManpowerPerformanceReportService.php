<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\HmData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ManpowerPerformanceReportService
{
    // V1 threshold (database/mpr -> DashboardController::mprIndex): ATR% >= 100 GOOD, >= 90 AVERAGE, < 90 POOR.
    private const STATUS_GOOD_MIN = 100;

    private const STATUS_AVERAGE_MIN = 90;

    public function __construct(private AttendanceRatioService $attendanceRatioService) {}

    /**
     * Build the month-by-month drilldown for one employee, from $monthsBack months ago through the current month.
     */
    public function monthlyBreakdown(Employee $employee, int $monthsBack): Collection
    {
        $periods = $this->buildPeriods($monthsBack);

        return $periods->map(fn (array $period) => $this->buildPeriodRow($employee, $period));
    }

    public function summarize(Collection $rows): array
    {
        $withAtr = $rows->filter(fn (array $row) => $row['atr'] !== null);

        $avgAtr = $withAtr->isEmpty() ? 0.0 : round($withAtr->avg('atr'), 2);

        return [
            'avgAtr' => $avgAtr,
            'totalHm' => round($rows->sum('hm'), 2),
            'status' => $this->classify($avgAtr),
        ];
    }

    private function buildPeriods(int $monthsBack): Collection
    {
        $start = now()->startOfMonth()->subMonths($monthsBack - 1);

        return collect(range(0, $monthsBack - 1))
            ->map(fn (int $offset) => $start->copy()->addMonths($offset))
            ->map(fn (Carbon $date) => [
                'bulan' => $date->month,
                'tahun' => $date->year,
                'label' => $date->translatedFormat('M-y'),
            ]);
    }

    private function buildPeriodRow(Employee $employee, array $period): array
    {
        $attendance = $this->attendanceFor($employee, $period['bulan'], $period['tahun']);
        $hmTotal = $this->hmTotalFor($employee, $period['bulan'], $period['tahun']);

        $atr = $attendance['atr'] ?? null;

        return [
            'periode' => $period['label'],
            'bulan' => $period['bulan'],
            'tahun' => $period['tahun'],
            'atr' => $atr,
            'sakit' => $attendance['sakit'] ?? 0,
            'izin' => $attendance['izin'] ?? 0,
            'alfa' => $attendance['alfa'] ?? 0,
            'hm' => $hmTotal,
            'status' => $atr === null ? null : $this->classify($atr),
        ];
    }

    private function attendanceFor(Employee $employee, int $bulan, int $tahun): ?array
    {
        return $this->attendanceRatioService->monthlyRatioForNrp($employee->nrp, $bulan, $tahun);
    }

    private function hmTotalFor(Employee $employee, int $bulan, int $tahun): float
    {
        return (float) HmData::query()
            ->where('employee_id', $employee->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->sum('hm_diff');
    }

    private function classify(float $atr): string
    {
        return match (true) {
            $atr >= self::STATUS_GOOD_MIN => 'GOOD',
            $atr >= self::STATUS_AVERAGE_MIN => 'AVERAGE',
            default => 'POOR',
        };
    }
}
