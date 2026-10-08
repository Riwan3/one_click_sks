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
    public const STATUS_LABEL = ['M' => 'M - Siap', 'G' => 'G - Gangguan', 'J' => 'J - Perbaikan'];

    private const DEFAULT_STATUS = 'M';

    private const LOADER_FIELDS = ['group_name', 'area', 'front', 'refueling_time', 'distance'];

    public function __construct(private FleetMasterService $master) {}

    public static function shiftNumber(string $shift): int
    {
        return ['D' => 1, 'N' => 2][$shift] ?? 1;
    }

    // ------------------------------------------------------------------- Baca

    public function view(string $date, string $shift): array
    {
        $rows = $this->rows($date, $shift)->get();
        $masters = FleetMasterUnit::query()->get()->keyBy(fn ($u) => Str::upper($u->code));
        $operators = $this->operatorCodes($date, $shift);
        $hasOperator = fn (string $code) => $operators === null ? null : isset($operators[Str::upper($code)]);

        $groups = $rows->groupBy('group_name')->map(fn (Collection $groupRows, $group) => [
            'name' => $group === '' ? 'TANPA GROUP' : $group,
            'loaders' => $groupRows->groupBy('loader_id')->map(fn (Collection $loaderRows, $code) => [
                'code' => (string) $code,
                'category' => $masters->get(Str::upper($code))?->category,
                'group_name' => $loaderRows->first()->group_name,
                'area' => $loaderRows->first()->area,
                'front' => $loaderRows->first()->front,
                'refueling_time' => $loaderRows->first()->refueling_time,
                'distance' => (int) $loaderRows->first()->distance,
                'has_operator' => $hasOperator($code),
                'haulers' => $loaderRows->whereNotNull('unit_nrp')->map(fn ($r) => [
                    'id' => $r->id, 'code' => $r->unit_nrp, 'status' => $r->unit_status ?: self::DEFAULT_STATUS,
                    'has_operator' => $hasOperator($r->unit_nrp),
                ])->values()->all(),
            ])->values()->all(),
        ])->values()->all();

        $placedLoaders = $rows->pluck('loader_id')->map(fn ($c) => Str::upper($c))->flip();
        $placedHaulers = $rows->pluck('unit_nrp')->filter()->map(fn ($c) => Str::upper($c))->flip();
        $availableHaulers = $this->activeOfType(FleetMasterUnit::HAULER)->reject(fn ($u) => $placedHaulers->has(Str::upper($u->code)))->values();

        return [
            'groups' => $groups,
            'available_loaders' => $this->activeOfType(FleetMasterUnit::LOADER)->reject(fn ($u) => $placedLoaders->has(Str::upper($u->code)))->values(),
            'available_haulers' => $availableHaulers,
            'summary' => [
                'loaders' => $placedLoaders->count(),
                'haulers' => $placedHaulers->count(),
                'loaders_without_hauler' => $rows->groupBy('loader_id')->filter(fn ($r) => $r->whereNotNull('unit_nrp')->isEmpty())->count(),
                'sisa' => $availableHaulers->count(),
            ],
            'warnings' => $this->warnings($rows, $operators),
            'group_options' => $this->master->groupOptions(),
        ];
    }

    // ------------------------------------------------------------------- Loader

    public function addLoader(string $date, string $shift, string $code, array $fields = []): void
    {
        $loader = $this->masterUnit(FleetMasterUnit::LOADER, $code);
        if ($this->loaderRows($date, $shift, $loader->code)->isNotEmpty()) {
            throw new InvalidArgumentException("Loader {$loader->code} sudah ada di setting ini.");
        }

        $defaults = [
            'group_name' => $loader->default_group, 'area' => $loader->default_area, 'front' => $loader->default_front,
            'refueling_time' => $loader->default_refueling_time, 'distance' => $loader->default_distance,
        ];
        $this->insertRow($date, $shift, $loader->code, $this->loaderAttributes($fields) + $defaults);
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
        $this->attachHauler($placeholder, $loaderRows->first(), $date, $shift, $hauler->code);
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

    public function setStatus(string $date, string $shift, string $haulerCode, string $status): void
    {
        $status = Str::upper(trim($status));
        if (! isset(self::STATUS_LABEL[$status])) throw new InvalidArgumentException('Status unit harus M, G, atau J.');

        $this->haulerRowOrFail($date, $shift, $haulerCode)->update(['unit_status' => $status]);
    }

    public function removeHauler(string $date, string $shift, string $haulerCode): void
    {
        $row = $this->haulerRowOrFail($date, $shift, $haulerCode);
        DB::transaction(function () use ($row) {
            $row->delete();
            $this->ensurePlaceholder($row);
        });
    }

    // ------------------------------------------------------------- Salin & hapus

    /** Salin seluruh setting dari tanggal+shift lain. Target yang sudah berisi hanya ditimpa bila $replace. */
    public function copy(string $date, string $shift, string $fromDate, string $fromShift, bool $replace): int
    {
        if ([$date, self::shiftNumber($shift)] === [$fromDate, self::shiftNumber($fromShift)]) {
            throw new InvalidArgumentException('Sumber salinan sama dengan tujuan.');
        }
        $source = $this->rows($fromDate, $fromShift)->get();
        if ($source->isEmpty()) throw new InvalidArgumentException('Setting sumber kosong, tidak ada yang disalin.');
        if (! $replace && $this->rows($date, $shift)->exists()) {
            throw new InvalidArgumentException('Tanggal dan shift tujuan sudah berisi setting.');
        }

        DB::transaction(function () use ($date, $shift, $source) {
            $this->rows($date, $shift)->delete();
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
    }

    // --------------------------------------------------------------- Internal

    private function rows(string $date, string $shift)
    {
        return FleetAllocation::query()->where('tanggal', $date)->where('shift', self::shiftNumber($shift))->orderBy('id');
    }

    private function loaderRows(string $date, string $shift, string $code): Collection
    {
        return $this->rows($date, $shift)->where('loader_id', trim($code))->get();
    }

    private function loaderRowsOrFail(string $date, string $shift, string $code): Collection
    {
        $rows = $this->loaderRows($date, $shift, $code);
        if ($rows->isEmpty()) throw new InvalidArgumentException("Loader $code belum ada di setting ini.");

        return $rows;
    }

    private function haulerRowOrFail(string $date, string $shift, string $code): FleetAllocation
    {
        $row = $this->rows($date, $shift)->where('unit_nrp', trim($code))->first();
        if (! $row) throw new InvalidArgumentException("Hauler $code belum dipasang di setting ini.");

        return $row;
    }

    /** Satu hauler tidak boleh ada di dua loader pada tanggal+shift yang sama. */
    private function guardNotPlaced(string $date, string $shift, string $haulerCode): void
    {
        $placed = $this->rows($date, $shift)->where('unit_nrp', $haulerCode)->first();
        if ($placed) throw new InvalidArgumentException("Hauler $haulerCode sudah dipasang di loader {$placed->loader_id}. Gunakan Pindah.");
    }

    private function masterUnit(string $type, string $code): FleetMasterUnit
    {
        $unit = FleetMasterUnit::active()->ofType($type)->where('code', trim($code))->first();
        if (! $unit) throw new InvalidArgumentException("Unit $code tidak ada di Master Unit (aktif) sebagai $type.");

        return $unit;
    }

    private function attachHauler(?FleetAllocation $placeholder, FleetAllocation $loaderRow, string $date, string $shift, string $haulerCode): void
    {
        $attributes = ['unit_nrp' => $haulerCode, 'unit_status' => self::DEFAULT_STATUS];
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
        foreach (array_intersect(self::LOADER_FIELDS, array_keys($fields)) as $key) {
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
