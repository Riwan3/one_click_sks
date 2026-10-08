<?php

namespace App\Services;

use App\Models\FleetAllocation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/** Baca-saja fleet_allocations: lembar Papan Fleet (Setting Board) dan ringkasan Monitoring Fleet per bulan. */
class FleetBoardService
{
    public const STATUSES = ['belum', 'dobel', 'kosong', 'terisi'];

    private const SHIFT_NUMBER = ['D' => 1, 'N' => 2];

    private const SIDE_GROUPS = ['ALL AREA', 'BREAKDOWN / SERVICE', 'STATUS / SUMMARY'];

    private const PAGE_DAYS = 7;

    public function ready(): bool
    {
        return Schema::hasTable('fleet_allocations');
    }

    /** Lembar visual satu tanggal + shift (D/N); null bila input tidak valid atau belum ada data. */
    public function sheet(string $date, string $shift): ?array
    {
        if (! $this->ready()) return null;
        if (! ManpowerV201SettingService::validDate($date) || ! ManpowerV201SettingService::validShift($shift)) return null;

        $rows = FleetAllocation::query()
            ->whereDate('tanggal', $date)->where('shift', self::SHIFT_NUMBER[$shift])
            ->orderBy('group_name')->orderBy('loader_id')->orderBy('unit_nrp')
            ->get();
        if ($rows->isEmpty()) return null;

        $groups = $rows->groupBy(fn ($r) => trim((string) $r->group_name) ?: 'LAINNYA')
            ->map(fn (Collection $items, string $name) => $this->group($name, $items))
            ->values();
        $isSide = fn ($group) => in_array($group['name'], self::SIDE_GROUPS, true);

        return [
            'date' => $date,
            'shift' => $shift,
            'main' => $groups->reject($isSide)->values()->all(),
            'side' => $groups->filter($isSide)->values()->all(),
            'total_hauler' => $groups->sum('hauler_total'),
        ];
    }

    /**
     * Monitoring sebulan ($month 'YYYY-MM'), 7 tanggal per halaman, urut kronologis.
     * Status baris: dobel (hauler di 2+ loader) > kosong (ada loader tanpa hauler) > terisi; belum = hari ini/besok tanpa data.
     * Return: ['month', 'rows', 'counts' (sebulan), 'pages' [[no, dari, sampai]], 'page'].
     */
    public function monitoring(string $month, string $statusFilter = '', ?int $page = null): array
    {
        $month = ManpowerV201SettingService::validMonth($month) ? $month : date('Y-m');
        $from = $month.'-01';
        $lastDay = (int) date('t', strtotime($from));

        $rows = $this->monthRows($from, date('Y-m-t', strtotime($from)));
        $counts = array_count_values(array_column($rows, 'status')) + array_fill_keys(self::STATUSES, 0);

        $totalPages = (int) ceil($lastDay / self::PAGE_DAYS);
        $pages = array_map(fn ($no) => [$no, ($no - 1) * self::PAGE_DAYS + 1, min($no * self::PAGE_DAYS, $lastDay)], range(1, $totalPages));
        $page = max(1, min($totalPages, $page ?? ($month === date('Y-m') ? (int) ceil((int) date('j') / self::PAGE_DAYS) : 1)));
        [, $dayFrom, $dayTo] = $pages[$page - 1];

        $isFiltered = in_array($statusFilter, self::STATUSES, true);
        $shown = array_values(array_filter($rows, fn ($r) => $r['day'] >= $dayFrom && $r['day'] <= $dayTo
            && (! $isFiltered || $r['status'] === $statusFilter)));

        return compact('month', 'counts', 'pages', 'page') + ['rows' => $shown];
    }

    private function group(string $name, Collection $items): array
    {
        $loaders = $items->groupBy(fn ($r) => trim((string) $r->loader_id))->map(function (Collection $lines, string $code) {
            $first = $lines->first();

            return [
                'code' => $code,
                'area' => (string) $first->area,
                'refueling' => (string) $first->refueling_time,
                'front' => (string) $first->front,
                'distance' => (int) $first->distance,
                'units' => $lines->filter(fn ($r) => trim((string) $r->unit_nrp) !== '')
                    ->map(fn ($r) => ['code' => trim($r->unit_nrp), 'status' => strtoupper(trim((string) $r->unit_status))])
                    ->values()->all(),
            ];
        })->values();

        return ['name' => $name, 'loaders' => $loaders->all(), 'hauler_total' => $loaders->sum(fn ($l) => count($l['units']))];
    }

    /** Satu baris per tanggal+shift yang punya data, ditambah 'belum' untuk hari ini & besok yang kosong. */
    private function monthRows(string $from, string $to): array
    {
        $byKey = FleetAllocation::query()
            ->whereBetween('tanggal', [$from, $to])
            ->get(['tanggal', 'shift', 'group_name', 'loader_id', 'unit_nrp', 'updated_at'])
            ->groupBy(fn ($r) => substr((string) $r->tanggal, 0, 10).'|'.$r->shift);

        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $rows = [];
        for ($ts = strtotime($from); $ts <= strtotime($to); $ts += 86400) {
            $date = date('Y-m-d', $ts);
            foreach (self::SHIFT_NUMBER as $shift => $number) {
                $lines = $byKey->get("$date|$number");
                $row = $lines ? $this->summarize($lines) : $this->emptyRow($date, $today, $tomorrow);
                if (! $row) continue;
                $rows[] = ['date' => $date, 'day' => (int) date('j', $ts), 'shift' => $shift] + $row;
            }
        }

        return $rows;
    }

    private function emptyRow(string $date, string $today, string $tomorrow): ?array
    {
        if ($date < $today || $date > $tomorrow) return null;

        return ['status' => 'belum', 'loaders' => 0, 'haulers' => 0, 'loaders_empty' => 0, 'doubles' => 0, 'updated_at' => null];
    }

    private function summarize(Collection $lines): array
    {
        $isMain = fn ($r) => ! in_array(trim((string) $r->group_name), self::SIDE_GROUPS, true);
        $withUnit = $lines->filter(fn ($r) => trim((string) $r->unit_nrp) !== '');
        $loaders = $lines->filter($isMain)->pluck('loader_id')->unique();
        $loadersWithUnit = $withUnit->filter($isMain)->pluck('loader_id')->unique();
        $doubles = $withUnit->groupBy('unit_nrp')->filter(fn ($g) => $g->pluck('loader_id')->unique()->count() > 1)->count();
        $empty = $loaders->count() - $loadersWithUnit->count();

        return [
            'status' => match (true) {
                $doubles > 0 => 'dobel',
                $empty > 0 => 'kosong',
                default => 'terisi',
            },
            'loaders' => $loaders->count(),
            'haulers' => $withUnit->pluck('unit_nrp')->unique()->count(),
            'loaders_empty' => $empty,
            'doubles' => $doubles,
            'updated_at' => $lines->max('updated_at')?->format('Y-m-d H:i:s'),
        ];
    }
}
