<?php

namespace App\Services;

use App\Models\MppDailyAdjustment;
use App\Models\MppKesiapanOperator;
use App\Models\MppRosterData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MppReportService
{
    public function generateDailyAtrReport(array $filters): Collection
    {
        $query = MppDailyAdjustment::query();

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->dateBetween($filters['date_from'], $filters['date_to']);
        }

        if (isset($filters['shift']) && $filters['shift']) {
            $query->byShift($filters['shift']);
        }

        if (isset($filters['kategori']) && $filters['kategori']) {
            $query->where('kategori', $filters['kategori']);
        }

        $adjustments = $query->latest('tanggal')->get();

        return $adjustments->map(function ($item) {
            $total_deductions = $item->sakit + $item->izin + $item->alfa + $item->training + $item->induksi + $item->tidak_checkin;

            return [
                'id' => $item->id,
                'tanggal' => $item->tanggal->format('Y-m-d'),
                'kategori' => $item->kategori,
                'shift' => $item->shift,
                'plan_mp' => $item->jumlah,
                'sakit' => $item->sakit,
                'izin' => $item->izin,
                'alfa' => $item->alfa,
                'training' => $item->training,
                'induksi' => $item->induksi,
                'tidak_checkin' => $item->tidak_checkin,
                'total_deductions' => $total_deductions,
                'actual_mp' => $item->jumlah - $total_deductions,
            ];
        });
    }

    public function generateActualMppReport(array $filters): Collection
    {
        $query = MppDailyAdjustment::query();

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->dateBetween($filters['date_from'], $filters['date_to']);
        }

        if (isset($filters['shift']) && $filters['shift']) {
            $query->byShift($filters['shift']);
        }

        if (isset($filters['kategori']) && $filters['kategori']) {
            $query->where('kategori', $filters['kategori']);
        }

        $adjustments = $query->latest('tanggal')->get();

        return $adjustments->map(function ($adj) {
            $deductions = $adj->sakit + $adj->izin + $adj->alfa +
                $adj->training + $adj->induksi + $adj->tidak_checkin;

            $actual_mp = $adj->jumlah - $deductions;
            $deviation = $this->calculateDeviationPercentage($adj->jumlah, $actual_mp);
            $achievement = ($actual_mp > 0) ? (($actual_mp / $adj->jumlah) * 100) : 0;

            return [
                'id' => $adj->id,
                'tanggal' => $adj->tanggal->format('Y-m-d'),
                'kategori' => $adj->kategori,
                'shift' => $adj->shift,
                'plan_mp' => $adj->jumlah,
                'deductions' => $deductions,
                'actual_mp' => $actual_mp,
                'deviation_percentage' => round($deviation, 2),
                'achievement_percentage' => round($achievement, 2),
            ];
        })->sortByDesc('achievement_percentage')->values();
    }

    public function generateKesiapanTrend(int $days = 30): Collection
    {
        $startDate = now()->subDays($days)->toDateString();

        $trend = MppKesiapanOperator::query()
            ->whereBetween('tanggal', [$startDate, now()->toDateString()])
            ->get()
            ->groupBy('csa_code')
            ->map(function ($group) {
                return $group->map(function ($item) {
                    $percentage = $item->operator_count > 0
                        ? (($item->ready_count / $item->operator_count) * 100)
                        : 0;

                    return [
                        'tanggal' => $item->tanggal->format('Y-m-d'),
                        'csa_code' => $item->csa_code,
                        'operator_count' => $item->operator_count,
                        'ready_count' => $item->ready_count,
                        'ready_percentage' => round($percentage, 2),
                        'status' => $percentage >= 95 ? 'Green' : ($percentage >= 90 ? 'Yellow' : 'Red'),
                    ];
                })->values();
            });

        return $trend;
    }

    public function calculateDeviationPercentage($plan, $actual): float
    {
        if ($plan <= 0) {
            return 0;
        }

        return (($actual - $plan) / $plan) * 100;
    }
}
