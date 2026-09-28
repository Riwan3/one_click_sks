<?php

namespace App\Services;

use App\Models\OdrAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Dashboard Compliance One-Reporting (padanan database/odr/dashboard_compliance.php v1).
 * KPI, grafik & ranking difilter tanggal + shift; filter area hanya berlaku untuk Outstanding Activity.
 */
class OdrComplianceDashboardService
{
    private const TREND_DAYS = 7;

    private const TOP_LIMIT = 10;

    private const SELESAI_SQL = "SUM(status = 'selesai')";

    public function summary(string $tanggal, string $shift): array
    {
        $row = $this->scoped($tanggal, $shift)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw(self::SELESAI_SQL.' AS selesai')
            ->selectRaw("SUM(status = 'progress') AS progress")
            ->first();

        $total = (int) $row->total;
        $selesai = (int) $row->selesai;
        $progress = (int) $row->progress;

        return [
            'total' => $total,
            'selesai' => $selesai,
            'progress' => $progress,
            'belum' => $total - $selesai - $progress,
            'persentase' => $this->percent($selesai, $total),
        ];
    }

    /** Selesai vs total per hari untuk 7 hari terakhir (s.d. hari ini, sama seperti v1). */
    public function dailyTrend(string $shift): Collection
    {
        $start = now()->subDays(self::TREND_DAYS - 1)->toDateString();
        $counts = OdrAssignment::query()
            ->whereBetween('tanggal', [$start, now()->toDateString()])
            ->when($shift !== '', fn ($q) => $q->where('shift', $shift))
            ->selectRaw('tanggal, COUNT(*) AS total')
            ->selectRaw(self::SELESAI_SQL.' AS selesai')
            ->groupBy('tanggal')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->tanggal)->toDateString());

        return collect(range(self::TREND_DAYS - 1, 0))->map(function ($daysAgo) use ($counts) {
            $date = now()->subDays($daysAgo);
            $row = $counts->get($date->toDateString());

            return ['tgl' => $date->format('d/m'), 'selesai' => (int) ($row->selesai ?? 0), 'total' => (int) ($row->total ?? 0)];
        });
    }

    public function perArea(string $tanggal, string $shift): Collection
    {
        return $this->grouped('area', $tanggal, $shift)->orderBy('area')->get()->map(fn ($r) => $this->withPercent($r, 'area'));
    }

    /** Pengawas dengan jumlah selesai terbanyak (untuk grafik doughnut). */
    public function perPengawas(string $tanggal, string $shift): Collection
    {
        return $this->grouped('pengawas', $tanggal, $shift)->orderByDesc('selesai')->limit(self::TOP_LIMIT)->get()
            ->map(fn ($r) => $this->withPercent($r, 'pengawas'));
    }

    public function topPengawas(string $tanggal, string $shift): Collection
    {
        return $this->grouped('pengawas', $tanggal, $shift)
            ->orderByRaw(self::SELESAI_SQL.' / COUNT(*) DESC')
            ->orderByDesc('selesai')
            ->limit(self::TOP_LIMIT)
            ->get()
            ->map(fn ($r) => $this->withPercent($r, 'pengawas'));
    }

    public function lowestAreas(string $tanggal, string $shift): Collection
    {
        return $this->grouped('area', $tanggal, $shift)
            ->orderByRaw(self::SELESAI_SQL.' / COUNT(*) ASC')
            ->limit(self::TOP_LIMIT)
            ->get()
            ->map(fn ($r) => $this->withPercent($r, 'area'));
    }

    public function outstanding(string $tanggal, string $shift, string $area): Collection
    {
        return $this->scoped($tanggal, $shift)
            ->where('status', 'belum')
            ->when($area !== '', fn ($q) => $q->where('area', $area))
            ->orderBy('area')
            ->orderBy('pengawas')
            ->get();
    }

    public function areaOptions(): Collection
    {
        return OdrAssignment::query()->whereNotNull('area')->where('area', '!=', '')->distinct()->orderBy('area')->pluck('area');
    }

    private function scoped(string $tanggal, string $shift): Builder
    {
        return OdrAssignment::query()
            ->whereDate('tanggal', $tanggal)
            ->when($shift !== '', fn ($q) => $q->where('shift', $shift));
    }

    private function grouped(string $column, string $tanggal, string $shift): Builder
    {
        return $this->scoped($tanggal, $shift)
            ->select($column)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw(self::SELESAI_SQL.' AS selesai')
            ->groupBy($column);
    }

    private function withPercent(object $row, string $labelColumn): array
    {
        return [
            'label' => $row->{$labelColumn},
            'total' => (int) $row->total,
            'selesai' => (int) $row->selesai,
            'persen' => $this->percent((int) $row->selesai, (int) $row->total),
        ];
    }

    private function percent(int $selesai, int $total): float
    {
        return $total > 0 ? round($selesai / $total * 100, 1) : 0;
    }
}
