<?php

namespace App\Services;

use App\Models\FleetAllocation;
use App\Models\FleetCluster;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Cluster setting fleet harian. Nama cluster == fleet_allocations.group_name (arti kolom lama tidak berubah);
 * area cluster disimpan di fleet_clusters. Cluster dari data lama (hanya ada di fleet_allocations) tetap dikenali.
 */
class FleetClusterService
{
    /** Cluster gabungan fleet_clusters + group_name di allocations (tanpa breakdown): [['name', 'area', 'sort_order'], ...] terurut. */
    public function list(string $date, string $shift): Collection
    {
        $stored = $this->stored($date, $shift);
        $legacy = $this->allocationNames($date, $shift)->reject(fn ($name) => $stored->has(Str::upper($name)));

        return $stored->map(fn ($c) => ['name' => $c->name, 'area' => $c->area, 'sort_order' => $c->sort_order])
            ->concat($legacy->map(fn ($name) => ['name' => $name, 'area' => null, 'sort_order' => 0]))
            ->sortBy([['sort_order', 'asc'], ['name', 'asc']])->values();
    }

    /** Baris fleet_clusters tanggal+shift untuk FleetAreaGrouping. */
    public function storedRows(string $date, string $shift): array
    {
        return $this->query($date, $shift)->get(['id', 'name', 'area', 'sort_order'])->toArray();
    }

    public function add(string $date, string $shift, string $name, ?string $area): void
    {
        $name = $this->cleanName($name);
        $this->guardUnique($date, $shift, $name);

        FleetCluster::create([
            'tanggal' => $date, 'shift' => FleetSettingService::shiftNumber($shift), 'name' => $name, 'area' => $this->cleanArea($area),
            'sort_order' => (int) $this->query($date, $shift)->max('sort_order') + 1,
            'created_by' => substr((string) Auth::user()?->username, 0, 50),
        ]);
    }

    /** Ubah nama/area; nama baru ikut meng-update group_name semua loader di cluster itu (tanggal + shift sama). */
    public function update(string $date, string $shift, string $name, string $newName, ?string $area): void
    {
        $this->guardExists($date, $shift, $name);
        $newName = $this->cleanName($newName);
        if (Str::upper($newName) !== Str::upper(trim($name))) $this->guardUnique($date, $shift, $newName);

        DB::transaction(function () use ($date, $shift, $name, $newName, $area) {
            $cluster = $this->query($date, $shift)->where('name', trim($name))->first()
                ?? FleetCluster::make(['tanggal' => $date, 'shift' => FleetSettingService::shiftNumber($shift), 'sort_order' => 0]);
            $cluster->fill(['name' => $newName, 'area' => $this->cleanArea($area)])->save();
            $this->allocations($date, $shift)->where('group_name', trim($name))->update(['group_name' => $newName]);
        });
    }

    /** Hapus cluster beserta loader-nya (hauler otomatis kembali ke Ready karena barisnya hilang). */
    public function remove(string $date, string $shift, string $name): void
    {
        $this->guardExists($date, $shift, $name);

        DB::transaction(function () use ($date, $shift, $name) {
            $this->allocations($date, $shift)->where('group_name', trim($name))->delete();
            $this->query($date, $shift)->where('name', trim($name))->delete();
        });
    }

    /** Nama cluster persis seperti tersimpan (case-insensitive), atau null bila belum ada. */
    public function resolveName(string $date, string $shift, string $name): ?string
    {
        return $this->list($date, $shift)->first(fn ($c) => Str::upper($c['name']) === Str::upper(trim($name)))['name'] ?? null;
    }

    public function exists(string $date, string $shift, string $name): bool
    {
        return $this->resolveName($date, $shift, $name) !== null;
    }

    public function clear(string $date, string $shift): void
    {
        $this->query($date, $shift)->delete();
    }

    public function isEmpty(string $date, string $shift): bool
    {
        return ! $this->query($date, $shift)->exists();
    }

    /** Salin cluster tersimpan dari tanggal+shift sumber (target dikosongkan lebih dulu oleh pemanggil). */
    public function copy(string $fromDate, string $fromShift, string $date, string $shift): void
    {
        $this->query($fromDate, $fromShift)->get()->each(fn ($c) => FleetCluster::create(
            ['tanggal' => $date, 'shift' => FleetSettingService::shiftNumber($shift)] + $c->only(['name', 'area', 'sort_order', 'created_by'])
        ));
    }

    /** Saran isian: nama cluster dan area yang pernah dipakai. */
    public function nameOptions(): array
    {
        return FleetCluster::query()->distinct()->pluck('name')
            ->merge(FleetAllocation::query()->whereNotNull('group_name')->distinct()->pluck('group_name'))
            ->filter(fn ($n) => $n && $n !== FleetSettingService::BREAKDOWN_GROUP)->unique()->sort()->values()->all();
    }

    public function areaOptions(): array
    {
        return FleetCluster::query()->whereNotNull('area')->distinct()->pluck('area')->filter()->sort()->values()->all();
    }

    // --------------------------------------------------------------- Internal

    private function query(string $date, string $shift)
    {
        return FleetCluster::query()->where('tanggal', $date)->where('shift', FleetSettingService::shiftNumber($shift));
    }

    private function allocations(string $date, string $shift)
    {
        return FleetAllocation::query()->where('tanggal', $date)->where('shift', FleetSettingService::shiftNumber($shift))
            ->whereRaw("COALESCE(group_name, '') <> ?", [FleetSettingService::BREAKDOWN_GROUP]);
    }

    private function stored(string $date, string $shift): Collection
    {
        return $this->query($date, $shift)->get()->keyBy(fn ($c) => Str::upper($c->name));
    }

    private function allocationNames(string $date, string $shift): Collection
    {
        return $this->allocations($date, $shift)->whereNotNull('group_name')->where('group_name', '<>', '')->distinct()->pluck('group_name');
    }

    private function cleanName(string $name): string
    {
        $name = trim($name);
        if ($name === '') throw new InvalidArgumentException('Nama cluster wajib diisi.');
        if (Str::upper($name) === FleetSettingService::BREAKDOWN_GROUP) throw new InvalidArgumentException('Nama cluster tersebut dipakai untuk panel Breakdown/Service.');

        return $name;
    }

    private function cleanArea(?string $area): ?string
    {
        return $area !== null && trim($area) !== '' ? trim($area) : null;
    }

    private function guardUnique(string $date, string $shift, string $name): void
    {
        if ($this->exists($date, $shift, $name)) throw new InvalidArgumentException("Cluster $name sudah ada di setting ini.");
    }

    private function guardExists(string $date, string $shift, string $name): void
    {
        if (! $this->exists($date, $shift, $name)) throw new InvalidArgumentException("Cluster $name belum ada di setting ini.");
    }
}
