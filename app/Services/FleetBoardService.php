<?php

namespace App\Services;

use App\Models\FleetAllocation;
use App\Models\FleetArea;
use App\Models\FleetCluster;
use App\Models\FleetMasterUnit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/** Baca-saja fleet_allocations + fleet_clusters: lembar Papan Fleet (Setting Board) dan ringkasan Monitoring Fleet per bulan. */
class FleetBoardService
{
    public const STATUSES = ['belum', 'dobel', 'kosong', 'terisi'];

    public const NO_AREA = 'TANPA AREA';

    private const SHIFT_NUMBER = ['D' => 1, 'N' => 2];

    private const BREAKDOWN_GROUP = 'BREAKDOWN / SERVICE';

    /** Grup non-loader pada monitoring (panel Breakdown + grup pembantu hasil impor Sheets lama). */
    private const SIDE_GROUPS = ['ALL AREA', self::BREAKDOWN_GROUP, 'STATUS / SUMMARY'];

    private const PAGE_DAYS = 7;

    public function ready(): bool
    {
        return Schema::hasTable('fleet_allocations');
    }

    /**
     * Lembar visual satu tanggal + shift (D/N); null bila input tidak valid atau belum ada cluster/loader.
     * Bentuk: ['date', 'shift', 'areas' => [['name', 'clusters' => [['name', 'loaders' => [...]]]]], 'breakdown' => ['units'] | null, 'total_hauler'].
     * Area urut sesuai urutan area ditambahkan (FleetAreaGrouping); area tanpa cluster tetap tampil; TANPA AREA paling bawah.
     */
    public function sheet(string $date, string $shift): ?array
    {
        if (! $this->ready()) return null;
        if (! ManpowerV201SettingService::validDate($date) || ! ManpowerV201SettingService::validShift($shift)) return null;

        $number = self::SHIFT_NUMBER[$shift];
        $all = FleetAllocation::query()->whereDate('tanggal', $date)->where('shift', $number)
            ->orderBy('loader_id')->orderBy('unit_nrp')->get();
        $byGroup = $all->groupBy(fn ($r) => FleetAreaGrouping::key($r->group_name));
        $groups = FleetAreaGrouping::group($this->storedClusters($date, $number), $all, true, $this->storedAreas($date, $number));
        $breakdown = $byGroup->get(self::BREAKDOWN_GROUP);
        if (! $groups && ! $breakdown) return null;

        $areas = collect($groups)->map(fn ($area) => [
            'name' => $area['name'] ? $this->areaLabel($area['name']) : self::NO_AREA,
            'clusters' => array_map(fn ($c) => ['name' => $c['name'], 'loaders' => $this->loaders($byGroup->get(FleetAreaGrouping::key($c['name']), collect()))], $area['clusters']),
        ]);

        $breakdownSheet = $this->breakdown($breakdown);
        $clusters = $areas->flatMap(fn ($a) => $a['clusters']);

        return [
            'date' => $date,
            'shift' => $shift,
            'areas' => $areas->all(),
            'breakdown' => $breakdownSheet,
            'ready' => $this->readyUnits($all),
            'summary' => $this->summary($clusters, $breakdownSheet),
            'total_hauler' => $clusters->sum(fn ($c) => $this->haulerCount($c)),
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

    private function haulerCount(array $cluster): int
    {
        return array_sum(array_map(fn ($loader) => count($loader['units']), $cluster['loaders']));
    }

    /** Ringkasan panel UNIT: COAL = hauler di cluster bernama COAL, OB = hauler cluster lain, STB/BD = unit breakdown. */
    private function summary(Collection $clusters, ?array $breakdown): array
    {
        $coal = $clusters->filter(fn ($c) => str_contains(mb_strtoupper($c['name']), 'COAL'))->sum(fn ($c) => $this->haulerCount($c));
        $ob = $clusters->sum(fn ($c) => $this->haulerCount($c)) - $coal;
        $bd = count($breakdown['units'] ?? []);

        return ['ob' => $ob, 'coal' => $coal, 'breakdown' => $bd, 'total' => $ob + $coal + $bd];
    }

    /** Hauler master aktif yang belum terpasang di loader mana pun maupun Breakdown: monte (parkiran M), gibson (G dan J), urut kode. */
    private function readyUnits(Collection $allocations): array
    {
        if (! Schema::hasTable('fleet_master_units')) return ['monte' => [], 'gibson' => []];

        $used = $allocations->pluck('unit_nrp')->map(fn ($code) => mb_strtoupper(trim((string) $code)))->filter()->flip();
        $ready = FleetMasterUnit::query()->active()->ofType(FleetMasterUnit::HAULER)->orderBy('code')->get(['code', 'parking_code'])
            ->reject(fn ($unit) => $used->has(mb_strtoupper(trim($unit->code))))
            ->map(fn ($unit) => ['code' => $unit->code, 'status' => (string) $unit->parking_code]);

        return [
            'monte' => $ready->where('status', 'M')->values()->all(),
            'gibson' => $ready->whereIn('status', ['G', 'J'])->values()->all(),
        ];
    }

    private function storedClusters(string $date, int $shift): Collection
    {
        if (! Schema::hasTable('fleet_clusters')) return collect();

        return FleetCluster::query()->whereDate('tanggal', $date)->where('shift', $shift)->get(['id', 'name', 'area', 'sort_order']);
    }

    private function storedAreas(string $date, int $shift): Collection
    {
        if (! Schema::hasTable('fleet_areas')) return collect();

        return FleetArea::query()->whereDate('tanggal', $date)->where('shift', $shift)->get(['id', 'name']);
    }

    private function areaLabel(string $area): string
    {
        $area = mb_strtoupper($area);

        return str_starts_with($area, 'AREA ') ? $area : "AREA $area";
    }

    private function loaders(Collection $lines): array
    {
        return $lines->groupBy(fn ($r) => trim((string) $r->loader_id))->reject(fn ($l, $code) => $code === '')->map(function (Collection $items, string $code) {
            $first = $items->first();

            return [
                'code' => $code,
                'dumping' => (string) $first->area,
                'front' => (string) $first->front,
                'refueling' => (string) $first->refueling_time,
                'distance' => (int) $first->distance,
                'units' => $this->units($items),
            ];
        })->values()->all();
    }

    private function units(Collection $lines): array
    {
        return $lines->filter(fn ($r) => trim((string) $r->unit_nrp) !== '')
            ->map(fn ($r) => ['code' => trim($r->unit_nrp), 'status' => strtoupper(trim((string) $r->unit_status))])
            ->values()->all();
    }

    private function breakdown(?Collection $lines): ?array
    {
        return $lines ? ['units' => $this->units($lines)] : null;
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
