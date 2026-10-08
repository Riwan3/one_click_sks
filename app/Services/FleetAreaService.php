<?php

namespace App\Services;

use App\Models\FleetArea;
use App\Models\FleetCluster;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/** Area setting fleet harian (induk cluster). Area boleh kosong; hapus area menghapus cluster-nya beserta loader (hauler kembali ke Ready). */
class FleetAreaService
{
    public function __construct(private FleetClusterService $clusters) {}

    /** Baris fleet_areas tanggal+shift untuk FleetAreaGrouping. */
    public function storedRows(string $date, string $shift): array
    {
        return $this->query($date, $shift)->get(['id', 'name'])->toArray();
    }

    public function add(string $date, string $shift, string $name): void
    {
        $name = $this->cleanName($name);
        $this->guardUnique($date, $shift, $name);

        FleetArea::create([
            'tanggal' => $date, 'shift' => FleetSettingService::shiftNumber($shift), 'name' => $name,
            'created_by' => substr((string) Auth::user()?->username, 0, 50),
        ]);
    }

    /** Ubah nama area; ikut meng-update fleet_clusters.area semua cluster di area itu (tanggal + shift sama). */
    public function rename(string $date, string $shift, string $name, string $newName): void
    {
        $area = $this->findOrFail($date, $shift, $name);
        $newName = $this->cleanName($newName);
        if (Str::upper($newName) !== Str::upper($area->name)) $this->guardUnique($date, $shift, $newName);

        DB::transaction(function () use ($date, $shift, $area, $newName) {
            $this->clustersIn($date, $shift, $area->name)->update(['area' => $newName]);
            $area->update(['name' => $newName]);
        });
    }

    /** Hapus area beserta semua cluster-nya (loader ikut terhapus, hauler kembali ke Ready). */
    public function remove(string $date, string $shift, string $name): void
    {
        $area = $this->findOrFail($date, $shift, $name);

        DB::transaction(function () use ($date, $shift, $area) {
            $this->clustersIn($date, $shift, $area->name)->pluck('name')
                ->each(fn ($cluster) => $this->clusters->remove($date, $shift, $cluster));
            $area->delete();
        });
    }

    public function clear(string $date, string $shift): void
    {
        $this->query($date, $shift)->delete();
    }

    public function isEmpty(string $date, string $shift): bool
    {
        return ! $this->query($date, $shift)->exists();
    }

    /** Salin area dari tanggal+shift sumber (target dikosongkan lebih dulu oleh pemanggil), urutan id dipertahankan. */
    public function copy(string $fromDate, string $fromShift, string $date, string $shift): void
    {
        $this->query($fromDate, $fromShift)->orderBy('id')->get()->each(fn ($a) => FleetArea::create(
            ['tanggal' => $date, 'shift' => FleetSettingService::shiftNumber($shift)] + $a->only(['name', 'created_by'])
        ));
    }

    // --------------------------------------------------------------- Internal

    private function query(string $date, string $shift)
    {
        return FleetArea::query()->where('tanggal', $date)->where('shift', FleetSettingService::shiftNumber($shift));
    }

    private function clustersIn(string $date, string $shift, string $areaName)
    {
        return FleetCluster::query()->where('tanggal', $date)->where('shift', FleetSettingService::shiftNumber($shift))->where('area', $areaName);
    }

    private function findOrFail(string $date, string $shift, string $name): FleetArea
    {
        return $this->query($date, $shift)->where('name', trim($name))->first()
            ?? throw new InvalidArgumentException("Area $name belum ada di setting ini.");
    }

    private function cleanName(string $name): string
    {
        $name = trim($name);
        if ($name === '') throw new InvalidArgumentException('Nama area wajib diisi.');

        return $name;
    }

    private function guardUnique(string $date, string $shift, string $name): void
    {
        if ($this->query($date, $shift)->where('name', $name)->exists()) throw new InvalidArgumentException("Area $name sudah ada di setting ini.");
    }
}
