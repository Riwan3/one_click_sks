<?php

namespace App\Services;

use App\Models\FleetAllocation;
use App\Models\FleetMasterUnit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Setting Fleet harian (per tanggal + shift) di tabel fleet_allocations: satu baris = satu hauler pada satu loader;
 * loader tanpa hauler = satu baris dengan unit_nrp NULL. Shift antarmuka 'D'/'N' dikonversi ke 1/2.
 */
class FleetSettingService
{
    /** Konvensi data lama & papan: unit breakdown/service = baris dengan group + loader_id ini, unit_nrp = kode unit. */
    public const BREAKDOWN_GROUP = 'BREAKDOWN / SERVICE';

    public const BREAKDOWN_LOADER = 'SERVICE & BD';

    private const LOADER_FIELDS = ['group_name', 'area', 'front', 'refueling_time', 'distance'];

    /** Field yang diisi/diubah user per loader (area = dumping point). Cluster (group_name) ditentukan konteks. */
    private const EDITABLE_FIELDS = ['area', 'front', 'refueling_time', 'distance'];

    public function __construct(private FleetClusterService $clusters) {}

    public static function shiftNumber(string $shift): int
    {
        return ['D' => 1, 'N' => 2][$shift] ?? 1;
    }

    // ------------------------------------------------------------------- Baca

    public function view(string $date, string $shift): array
    {
        $all = $this->rows($date, $shift)->get();
        [$breakdownRows, $rows] = $all->partition(fn ($r) => $r->group_name === self::BREAKDOWN_GROUP);
        $masters = FleetMasterUnit::query()->get()->keyBy(fn ($u) => Str::upper($u->code));
        $operators = $this->operatorCodes($date, $shift);
        $hasOperator = fn (string $code) => $operators === null ? null : isset($operators[Str::upper($code)]);

        $loaderCards = fn (Collection $groupRows) => $groupRows->groupBy('loader_id')->map(fn (Collection $loaderRows, $code) => [
            'code' => (string) $code,
            'category' => $masters->get(Str::upper($code))?->category,
            'area' => $loaderRows->first()->area,
            'front' => $loaderRows->first()->front,
            'refueling_time' => $loaderRows->first()->refueling_time,
            'distance' => (int) $loaderRows->first()->distance,
            'has_operator' => $hasOperator($code),
            'haulers' => $loaderRows->whereNotNull('unit_nrp')->map(fn ($r) => [
                'id' => $r->id, 'code' => $r->unit_nrp, 'parking_code' => $r->unit_status ?: null,
                'has_operator' => $hasOperator($r->unit_nrp),
            ])->values()->all(),
        ])->values()->all();

        $byCluster = $rows->groupBy(fn ($r) => Str::upper((string) $r->group_name));
        $areas = collect(FleetAreaGrouping::group($this->clusters->storedRows($date, $shift), $rows, true))->map(fn ($area) => [
            'name' => $area['name'],
            'clusters' => collect($area['clusters'])->map(fn ($cluster) => [
                'name' => $cluster['name'] === '' ? 'TANPA CLUSTER' : $cluster['name'],
                'area' => $cluster['area'],
                'is_editable' => $cluster['name'] !== '',
                'loaders' => $loaderCards($byCluster->get(Str::upper($cluster['name']), collect())),
            ])->all(),
        ])->all();
        $placedLoaders = $rows->pluck('loader_id')->map(fn ($c) => Str::upper($c))->flip();
        $placedHaulers = $rows->pluck('unit_nrp')->filter()->map(fn ($c) => Str::upper($c))->flip();
        $breakdown = $breakdownRows->whereNotNull('unit_nrp')->map(fn ($r) => [
            'id' => $r->id, 'code' => $r->unit_nrp, 'type' => $masters->get(Str::upper($r->unit_nrp))?->unit_type, 'parking_code' => $r->unit_status ?: null,
        ])->values()->all();
        $inBreakdown = collect($breakdown)->pluck('code')->map(fn ($c) => Str::upper($c))->flip();
        $isFree = fn ($u) => ! $placedHaulers->has(Str::upper($u->code)) && ! $inBreakdown->has(Str::upper($u->code));
        $availableHaulers = $this->activeOfType(FleetMasterUnit::HAULER)->filter($isFree)->values();

        return [
            'areas' => $areas,
            'available_loaders' => $this->activeOfType(FleetMasterUnit::LOADER)->reject(fn ($u) => $placedLoaders->has(Str::upper($u->code)) || $inBreakdown->has(Str::upper($u->code)))->values(),
            'breakdown' => $breakdown,
            'available_haulers' => $availableHaulers,
            'summary' => [
                'clusters' => collect($areas)->flatMap(fn ($area) => $area['clusters'])->where('is_editable', true)->count(),
                'loaders' => $placedLoaders->count(),
                'haulers' => $placedHaulers->count(),
                'loaders_without_hauler' => $rows->groupBy('loader_id')->filter(fn ($r) => $r->whereNotNull('unit_nrp')->isEmpty())->count(),
                'sisa' => $availableHaulers->count(),
            ],
            'warnings' => $this->warnings($rows, $operators),
            'cluster_options' => $this->clusters->nameOptions(),
            'area_options' => $this->clusters->areaOptions(),
        ];
    }

    // ------------------------------------------------------------------- Loader

    public function addLoader(string $date, string $shift, string $code, string $cluster, array $fields = []): void
    {
        $clusterName = $this->clusters->resolveName($date, $shift, $cluster)
            ?? throw new InvalidArgumentException('Pilih cluster tujuan loader (buat cluster dulu bila belum ada).');
        $loader = $this->masterUnit(FleetMasterUnit::LOADER, $code);
        if ($this->breakdownRows($date, $shift)->where('unit_nrp', $loader->code)->exists()) {
            throw new InvalidArgumentException("Loader {$loader->code} sedang di Breakdown/Service. Kembalikan ke Ready dulu.");
        }
        if ($this->loaderRows($date, $shift, $loader->code)->isNotEmpty()) {
            throw new InvalidArgumentException("Loader {$loader->code} sudah ada di setting ini.");
        }

        $this->insertRow($date, $shift, $loader->code, $this->loaderAttributes($fields) + ['group_name' => $clusterName]);
    }

    public function updateLoader(string $date, string $shift, string $code, array $fields): void
    {
        $rows = $this->loaderRowsOrFail($date, $shift, $code);
        $rows->each->update($this->loaderAttributes($fields));
    }

    public function removeLoader(string $date, string $shift, string $code): void
    {
        $this->loaderRowsOrFail($date, $shift, $code)->each->delete();
    }

    // ------------------------------------------------------------------- Hauler

    public function assignHauler(string $date, string $shift, string $loaderCode, string $haulerCode): void
    {
        $loaderRows = $this->loaderRowsOrFail($date, $shift, $loaderCode);
        $hauler = $this->masterUnit(FleetMasterUnit::HAULER, $haulerCode);
        $this->guardNotPlaced($date, $shift, $hauler->code);

        $placeholder = $loaderRows->firstWhere('unit_nrp', null);
        $this->attachHauler($placeholder, $loaderRows->first(), $date, $shift, $hauler);
    }

    public function moveHauler(string $date, string $shift, string $haulerCode, string $toLoaderCode): void
    {
        $row = $this->haulerRowOrFail($date, $shift, $haulerCode);
        $targetRows = $this->loaderRowsOrFail($date, $shift, $toLoaderCode);
        if (Str::upper($row->loader_id) === Str::upper($toLoaderCode)) return;

        $source = $row->replicate();
        DB::transaction(function () use ($row, $targetRows, $source) {
            $targetRows->firstWhere('unit_nrp', null)?->delete();
            $row->update($targetRows->first()->only(['group_name', 'loader_id', 'area', 'front', 'refueling_time', 'distance']));
            $this->ensurePlaceholder($source);
        });
    }


    // ------------------------------------------------------------ Breakdown / Service

    /** Kirim unit (hauler terpasang / hauler ready / loader) ke Breakdown/Service; loader dilepas dari setting dan haulernya kembali ke Ready. */
    public function sendToBreakdown(string $date, string $shift, string $unitCode): void
    {
        $code = trim($unitCode);
        if ($this->breakdownRows($date, $shift)->where('unit_nrp', $code)->exists()) {
            throw new InvalidArgumentException("Unit $code sudah di Breakdown/Service.");
        }

        $haulerRow = $this->layoutRows($date, $shift)->where('unit_nrp', $code)->first();
        $master = FleetMasterUnit::active()->where('code', $code)->first();
        if (! $haulerRow && ! $master) throw new InvalidArgumentException("Unit $code tidak ada di Master Unit (aktif).");

        DB::transaction(function () use ($date, $shift, $code, $haulerRow, $master) {
            $parking = $haulerRow ? $haulerRow->unit_status : $master->parking_code;
            $haulerRow ? $this->releaseRow($haulerRow) : $this->layoutRows($date, $shift)->where('loader_id', $code)->delete();
            $this->insertRow($date, $shift, self::BREAKDOWN_LOADER, ['group_name' => self::BREAKDOWN_GROUP, 'unit_nrp' => $code, 'unit_status' => $parking ?: null]);
        });
    }

    /** Kembalikan unit dari Breakdown/Service ke Ready. */
    public function returnFromBreakdown(string $date, string $shift, string $unitCode): void
    {
        $deleted = $this->breakdownRows($date, $shift)->where('unit_nrp', trim($unitCode))->delete();
        if (! $deleted) throw new InvalidArgumentException("Unit $unitCode tidak ada di Breakdown/Service.");
    }

    public function removeHauler(string $date, string $shift, string $haulerCode): void
    {
        $row = $this->haulerRowOrFail($date, $shift, $haulerCode);
        DB::transaction(fn () => $this->releaseRow($row));
    }

    // ------------------------------------------------------------- Salin & hapus

    /** Salin seluruh setting dari tanggal+shift lain. Target yang sudah berisi hanya ditimpa bila $replace. */
    public function copy(string $date, string $shift, string $fromDate, string $fromShift, bool $replace): int
    {
        if ([$date, self::shiftNumber($shift)] === [$fromDate, self::shiftNumber($fromShift)]) {
            throw new InvalidArgumentException('Sumber salinan sama dengan tujuan.');
        }
        $source = $this->rows($fromDate, $fromShift)->get();
        if ($source->isEmpty() && $this->clusters->isEmpty($fromDate, $fromShift)) throw new InvalidArgumentException('Setting sumber kosong, tidak ada yang disalin.');
        if (! $replace && ($this->rows($date, $shift)->exists() || ! $this->clusters->isEmpty($date, $shift))) {
            throw new InvalidArgumentException('Tanggal dan shift tujuan sudah berisi setting.');
        }

        DB::transaction(function () use ($date, $shift, $fromDate, $fromShift, $source) {
            $this->clear($date, $shift);
            $this->clusters->copy($fromDate, $fromShift, $date, $shift);
            $source->each(fn ($r) => FleetAllocation::create(
                ['tanggal' => $date, 'shift' => self::shiftNumber($shift)] + $r->only([
                    'group_name', 'loader_id', 'area', 'front', 'refueling_time', 'unit_nrp', 'unit_status', 'distance',
                ])
            ));
        });

        return $source->count();
    }

    public function clear(string $date, string $shift): void
    {
        $this->rows($date, $shift)->delete();
        $this->clusters->clear($date, $shift);
    }

    // --------------------------------------------------------------- Internal

    /** Baris breakdown/service (panel samping). */
    private function breakdownRows(string $date, string $shift)
    {
        return $this->rows($date, $shift)->where('group_name', self::BREAKDOWN_GROUP);
    }

    /** Baris loader/hauler pada susunan setting (tanpa baris breakdown). */
    private function layoutRows(string $date, string $shift)
    {
        return $this->rows($date, $shift)->whereRaw("COALESCE(group_name, '') <> ?", [self::BREAKDOWN_GROUP]);
    }

    /** Hapus baris hauler; loader yang kehilangan hauler terakhir tetap tampil (placeholder). */
    private function releaseRow(FleetAllocation $row): void
    {
        $row->delete();
        $this->ensurePlaceholder($row);
    }

    private function rows(string $date, string $shift)
    {
        return FleetAllocation::query()->where('tanggal', $date)->where('shift', self::shiftNumber($shift))->orderBy('id');
    }

    private function loaderRows(string $date, string $shift, string $code): Collection
    {
        return $this->layoutRows($date, $shift)->where('loader_id', trim($code))->get();
    }

    private function loaderRowsOrFail(string $date, string $shift, string $code): Collection
    {
        $rows = $this->loaderRows($date, $shift, $code);
        if ($rows->isEmpty()) throw new InvalidArgumentException("Loader $code belum ada di setting ini.");

        return $rows;
    }

    private function haulerRowOrFail(string $date, string $shift, string $code): FleetAllocation
    {
        $row = $this->layoutRows($date, $shift)->where('unit_nrp', trim($code))->first();
        if (! $row) throw new InvalidArgumentException("Hauler $code belum dipasang di setting ini.");

        return $row;
    }

    /** Satu hauler tidak boleh ada di dua loader pada tanggal+shift yang sama. */
    private function guardNotPlaced(string $date, string $shift, string $haulerCode): void
    {
        $placed = $this->rows($date, $shift)->where('unit_nrp', $haulerCode)->first();
        if (! $placed) return;

        $where = $placed->group_name === self::BREAKDOWN_GROUP ? 'di Breakdown/Service. Kembalikan ke Ready dulu' : "di loader {$placed->loader_id}. Gunakan Pindah";
        throw new InvalidArgumentException("Hauler $haulerCode sudah $where.");
    }

    private function masterUnit(string $type, string $code): FleetMasterUnit
    {
        $unit = FleetMasterUnit::active()->ofType($type)->where('code', trim($code))->first();
        if (! $unit) throw new InvalidArgumentException("Unit $code tidak ada di Master Unit (aktif) sebagai $type.");

        return $unit;
    }

    private function attachHauler(?FleetAllocation $placeholder, FleetAllocation $loaderRow, string $date, string $shift, FleetMasterUnit $hauler): void
    {
        $attributes = ['unit_nrp' => $hauler->code, 'unit_status' => $hauler->parking_code]; // kopi parkiran master (NULL bila kosong)
        if ($placeholder) {
            $placeholder->update($attributes);
            return;
        }

        $this->insertRow($date, $shift, $loaderRow->loader_id, $loaderRow->only(self::LOADER_FIELDS) + $attributes);
    }

    private function insertRow(string $date, string $shift, string $loaderCode, array $attributes): void
    {
        FleetAllocation::create(array_merge(
            ['distance' => 0], $attributes,
            ['tanggal' => $date, 'shift' => self::shiftNumber($shift), 'loader_id' => $loaderCode],
        ));
    }

    /** Loader yang kehilangan hauler terakhir tetap tampil: satu baris kosong (unit_nrp NULL). */
    private function ensurePlaceholder(FleetAllocation $row): void
    {
        $stillExists = FleetAllocation::where(['tanggal' => $row->tanggal, 'shift' => $row->shift, 'loader_id' => $row->loader_id])->exists();
        if ($stillExists) return;

        FleetAllocation::create($row->only(['tanggal', 'shift', 'group_name', 'loader_id', 'area', 'front', 'refueling_time', 'distance']) + ['unit_nrp' => null, 'unit_status' => null]);
    }

    private function loaderAttributes(array $fields): array
    {
        $attributes = [];
        foreach (array_intersect(self::EDITABLE_FIELDS, array_keys($fields)) as $key) {
            $value = $fields[$key];
            $attributes[$key] = $key === 'distance' ? max(0, (int) $value) : (trim((string) $value) === '' ? null : trim((string) $value));
        }

        return $attributes;
    }

    private function activeOfType(string $type): Collection
    {
        return FleetMasterUnit::active()->ofType($type)->orderBy('sort_order')->orderBy('code')->get();
    }

    // ------------------------------------------------------ Peringatan operator

    /** Kode unit yang punya operator di Setting Manpower (tanggal+shift); null bila setting manpower belum ada. */
    private function operatorCodes(string $date, string $shift): ?array
    {
        $isReady = Schema::hasTable('v2_mp_settings') && Schema::hasTable('v2_mp_assignments') && Schema::hasTable('v2_mp_units');
        $settingId = $isReady ? DB::table('v2_mp_settings')->where(['work_date' => $date, 'shift' => $shift])->value('id') : null;
        if (! $settingId) return null;

        return DB::table('v2_mp_assignments as a')->join('v2_mp_units as u', 'u.id', '=', 'a.unit_id')
            ->where('a.setting_id', $settingId)->pluck('u.unit')
            ->mapWithKeys(fn ($code) => [Str::upper(trim($code)) => true])->all();
    }

    private function warnings(Collection $rows, ?array $operators): array
    {
        if ($rows->isEmpty()) return [];
        if ($operators === null) return ['Setting Manpower untuk tanggal dan shift ini belum dibuat, ketersediaan operator tidak bisa dicek.'];

        $missing = $rows->pluck('loader_id')->merge($rows->pluck('unit_nrp'))->filter()->unique()
            ->reject(fn ($code) => isset($operators[Str::upper(trim($code))]))->values();
        if ($missing->isEmpty()) return [];

        return [$missing->count().' unit belum punya operator di Setting Manpower: '.$missing->take(15)->implode(', ').($missing->count() > 15 ? ', ...' : '').'.'];
    }
}
