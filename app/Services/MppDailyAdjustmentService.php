<?php

namespace App\Services;

use App\Models\MppDailyAdjustment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MppDailyAdjustmentService
{
    public function create(array $data): MppDailyAdjustment
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return MppDailyAdjustment::create($data);
    }

    public function update(MppDailyAdjustment $adjustment, array $data): MppDailyAdjustment
    {
        $data['updated_by'] = auth()->id();
        $adjustment->update($data);

        return $adjustment->refresh();
    }

    public function delete(MppDailyAdjustment $adjustment): bool
    {
        return $adjustment->delete();
    }

    public function getByDateAndShift(Carbon $date, string $shift = 'D'): Collection
    {
        return MppDailyAdjustment::whereDate('tanggal', $date)
            ->byShift($shift)
            ->get();
    }

    public function getByDateRange(Carbon $startDate, Carbon $endDate, string $shift = 'D'): Collection
    {
        return MppDailyAdjustment::dateBetween($startDate, $endDate)
            ->byShift($shift)
            ->get();
    }

    public function calculateActualMp(MppDailyAdjustment $adjustment): int
    {
        $deductions = $adjustment->sakit + $adjustment->izin + $adjustment->alfa
            + $adjustment->training + $adjustment->induksi + $adjustment->tidak_checkin;

        return max(0, $adjustment->jumlah - $deductions);
    }

    public function calculateDeviation(int $actualMp, int $planMp): float
    {
        if ($planMp === 0) {
            return 0;
        }

        return (($actualMp - $planMp) / $planMp) * 100;
    }

    public function upsertDailyData(array $data): MppDailyAdjustment
    {
        return DB::transaction(function () use ($data) {
            $existing = MppDailyAdjustment::where([
                'tanggal' => $data['tanggal'],
                'shift' => $data['shift'] ?? 'D',
                'kategori' => $data['kategori'],
            ])->first();

            if ($existing) {
                return $this->update($existing, $data);
            }

            return $this->create($data);
        });
    }
}
