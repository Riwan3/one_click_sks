<?php

namespace App\Services;

use App\Models\OdrAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** Report Compliance One-Reporting: Laporan, Rekap Harian & Rekap Bulanan (padanan laporan_compliance.php v1). */
class OdrComplianceReportService
{
    public const TABS = ['laporan', 'rekap_harian', 'rekap_bulanan'];

    private const TOP_PENGAWAS = 12;

    private const STATUS_LABELS = ['selesai' => 'Complete', 'progress' => 'Progress', 'belum' => 'Belum Dimulai'];

    public function normalizeFilters(array $input): array
    {
        $text = fn ($key) => trim((string) ($input[$key] ?? ''));
        $date = fn ($key, $default) => strtotime($text($key)) ? Carbon::parse($text($key))->toDateString() : $default;
        $bulan = (int) ($input['bulan'] ?? 0);
        $tahun = (int) ($input['tahun'] ?? 0);

        return [
            'tab' => in_array($text('tab'), self::TABS, true) ? $text('tab') : 'laporan',
            'tgl_dari' => $date('tgl_dari', now()->startOfMonth()->toDateString()),
            'tgl_sampai' => $date('tgl_sampai', now()->toDateString()),
            'area' => $text('area'),
            'pengawas' => $text('pengawas'),
            'status' => array_key_exists($text('status'), self::STATUS_LABELS) ? $text('status') : '',
            'bulan' => $bulan >= 1 && $bulan <= 12 ? $bulan : (int) now()->month,
            'tahun' => $tahun >= 2000 ? $tahun : (int) now()->year,
        ];
    }

    /** Tab Laporan: detail assignment per rentang tanggal + area/pengawas/status. */
    public function reportRows(array $filters): Collection
    {
        return $this->inRange($filters)
            ->when($filters['pengawas'], fn ($q, $v) => $q->where('pengawas', 'like', "%{$v}%"))
            ->when($filters['status'], fn ($q, $v) => $q->where('status', $v))
            ->orderByDesc('tanggal')
            ->orderBy('area')
            ->get();
    }

    /** Tab Rekap Harian: jumlah per status & rata-rata compliance per tanggal (filter area saja). */
    public function dailyRecap(array $filters): Collection
    {
        return $this->inRange($filters)->orderByDesc('tanggal')->get()
            ->groupBy(fn (OdrAssignment $row) => Carbon::parse($row->tanggal)->toDateString())
            ->map(fn (Collection $rows, string $tanggal) => [
                'tanggal' => $tanggal,
                'total' => $rows->count(),
                'selesai' => $rows->where('status', 'selesai')->count(),
                'progress' => $rows->where('status', 'progress')->count(),
                'belum' => $rows->whereNotIn('status', ['selesai', 'progress'])->count(),
                'pct' => round($rows->avg(fn (OdrAssignment $row) => $row->compliancePercent()), 1),
            ])
            ->values();
    }

    /** Tab Rekap Bulanan: rata-rata compliance per hari, per pengawas (top 12) & per area. */
    public function monthlyRecap(int $bulan, int $tahun): array
    {
        $rows = OdrAssignment::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();
        $average = fn (Collection $group) => round($group->avg(fn (OdrAssignment $row) => $row->compliancePercent()), 1);
        $toSeries = fn (Collection $grouped) => $grouped->map(fn ($avg, $label) => ['label' => (string) $label, 'pct' => $avg])->values();

        return [
            'harian' => $toSeries($rows->groupBy(fn ($row) => Carbon::parse($row->tanggal)->day)->map($average)->sortKeys()),
            'pengawas' => $toSeries($rows->groupBy('pengawas')->map($average)->sortDesc()->take(self::TOP_PENGAWAS)),
            'area' => $toSeries($rows->groupBy('area')->map($average)->sortDesc()),
        ];
    }

    public function areaOptions(): Collection
    {
        return OdrAssignment::whereNotNull('area')->where('area', '!=', '')->distinct()->orderBy('area')->pluck('area');
    }

    /** Baris CSV export (header dinamis Task 1..N, sama seperti v1). */
    public function exportRows(array $filters): array
    {
        $rows = $this->reportRows($filters);
        $taskColumns = max(1, (int) $rows->max(fn (OdrAssignment $row) => count($row->tasks())));

        $header = ['No', 'Tanggal', 'Area', 'Unit', 'Pengawas'];
        foreach (range(1, $taskColumns) as $i) $header[] = "Task {$i}";

        $lines = $rows->values()->map(function (OdrAssignment $row, int $idx) use ($taskColumns) {
            $tasks = $row->tasks();
            $done = $row->doneFlags();
            $taskCells = array_map(fn ($i) => isset($tasks[$i]) ? ($done[$i] ? '✓ ' : '✗ ').$tasks[$i] : '', range(0, $taskColumns - 1));

            return [
                $idx + 1, Carbon::parse($row->tanggal)->toDateString(), $row->area, $row->unit, $row->pengawas,
                ...$taskCells,
                $row->compliancePercent().'%',
                self::STATUS_LABELS[$row->status] ?? 'Belum Dimulai',
                $row->keterangan ?? '',
            ];
        });

        return [[...$header, 'Compliance %', 'Status', 'Keterangan'], ...$lines->all()];
    }

    private function inRange(array $filters): Builder
    {
        return OdrAssignment::query()
            ->whereBetween('tanggal', [$filters['tgl_dari'], $filters['tgl_sampai']])
            ->when($filters['area'], fn ($q, $v) => $q->where('area', $v));
    }
}
