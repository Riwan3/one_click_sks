<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Pengelompokan Area -> Cluster untuk satu tanggal + shift (pure, tanpa query). Dipakai halaman Setting Fleet dan Papan Fleet.
 *
 * Input:
 *  - $clusters: baris fleet_clusters [['id', 'name', 'area', 'sort_order'], ...]
 *  - $rows: baris fleet_allocations [['id', 'group_name', 'loader_id'], ...] (array atau objek; baris tanpa loader_id diabaikan)
 *  - $areas: baris fleet_areas [['id', 'name'], ...] (area yang sengaja dibuat, boleh kosong)
 *
 * Output `group()`: list terurut `[['name' => ?string, 'id' => ?int, 'clusters' => [cluster, ...]], ...]` dengan cluster =
 *  ['name', 'area', 'id' (null bila hanya ada di allocations), 'loader_count', 'first_row_id'].
 *
 * Aturan urutan:
 *  - Kunci area = trim + case-insensitive; nama tampil = penulisan dari baris fleet_areas (atau cluster pertama bila fallback).
 *  - Urutan AREA = fleet_areas.id naik (urutan area ditambahkan). Area yang hanya ada di fleet_clusters.area (tanpa baris
 *    fleet_areas) menyusul setelahnya, diurut MIN(fleet_clusters.id).
 *  - Urutan CLUSTER di dalam area = fleet_clusters.id naik (urutan ditambahkan; sort_order tidak dipakai karena searah id).
 *  - Cluster tanpa area, atau yang hanya ada di allocations (data lama/impor), masuk pita `name = null` ("TANPA AREA") yang
 *    SELALU paling bawah, diurut MIN(fleet_allocations.id) lalu nama.
 *  - Group 'BREAKDOWN / SERVICE' selalu dikecualikan. Baris tanpa group_name menjadi cluster bernama '' (pemanggil memberi label).
 *  - $includeEmpty = false: cluster tanpa loader dan area tanpa cluster dikeluarkan. true: keduanya tetap tampil (pita kosong).
 */
class FleetAreaGrouping
{
    public static function group(iterable $clusters, iterable $rows, bool $includeEmpty = false, iterable $areas = []): array
    {
        $loaders = self::loaderStats(collect($rows));
        $stored = collect($clusters)->sortBy(fn ($c) => (int) data_get($c, 'id'))->values();
        $storedKeys = $stored->map(fn ($c) => self::key(data_get($c, 'name')))->flip();

        $items = $stored->map(fn ($c) => self::item($c, $loaders->get(self::key(data_get($c, 'name')))))
            ->concat($loaders->reject(fn ($stat, $key) => $storedKeys->has($key))->map(fn ($stat) => self::item(['id' => null, 'name' => $stat['name'], 'area' => null], $stat))->values());

        if (! $includeEmpty) $items = $items->filter(fn ($item) => $item['loader_count'] > 0);

        $byArea = $items->filter(fn ($item) => self::key($item['area']) !== '')->groupBy(fn ($item) => self::key($item['area']));
        $declared = collect($areas)->sortBy(fn ($a) => (int) data_get($a, 'id'))->unique(fn ($a) => self::key(data_get($a, 'name')))->values();
        $declaredKeys = $declared->map(fn ($a) => self::key(data_get($a, 'name')))->flip();

        $fromAreas = $declared->map(fn ($a) => [
            'name' => trim((string) data_get($a, 'name')),
            'id' => (int) data_get($a, 'id'),
            'clusters' => $byArea->get(self::key(data_get($a, 'name')), collect())->values()->all(),
        ]);
        $fallback = $byArea->reject(fn ($group, $key) => $declaredKeys->has($key))
            ->sortBy(fn (Collection $group) => $group->min('id'))
            ->map(fn (Collection $group) => ['name' => $group->first()['area'], 'id' => null, 'clusters' => $group->values()->all()]);

        $areaList = $fromAreas->concat($fallback)->values();
        if (! $includeEmpty) $areaList = $areaList->filter(fn ($area) => $area['clusters'])->values();

        $unassigned = $items->filter(fn ($item) => self::key($item['area']) === '')
            ->sort(fn ($a, $b) => [$a['first_row_id'] ?? PHP_INT_MAX, $a['name']] <=> [$b['first_row_id'] ?? PHP_INT_MAX, $b['name']])->values();

        return $areaList->when($unassigned->isNotEmpty(), fn ($list) => $list->push(['name' => null, 'id' => null, 'clusters' => $unassigned->all()]))->values()->all();
    }

    public static function key(?string $value): string
    {
        return Str::upper(trim((string) $value));
    }

    /** Statistik loader per cluster (kunci = nama ter-normalisasi): nama tampil, jumlah loader unik, id baris terkecil. */
    private static function loaderStats(Collection $rows): Collection
    {
        return $rows->filter(fn ($r) => trim((string) data_get($r, 'loader_id')) !== '' && trim((string) data_get($r, 'group_name')) !== FleetSettingService::BREAKDOWN_GROUP)
            ->groupBy(fn ($r) => self::key(data_get($r, 'group_name')))
            ->map(fn (Collection $group) => [
                'name' => trim((string) data_get($group->sortBy(fn ($r) => (int) data_get($r, 'id'))->first(), 'group_name')),
                'loader_count' => $group->pluck('loader_id')->map(fn ($c) => self::key($c))->unique()->count(),
                'first_row_id' => (int) $group->min(fn ($r) => (int) data_get($r, 'id')),
            ]);
    }

    private static function item(mixed $cluster, ?array $stat): array
    {
        return [
            'id' => data_get($cluster, 'id'),
            'name' => trim((string) data_get($cluster, 'name')),
            'area' => ($area = trim((string) data_get($cluster, 'area'))) === '' ? null : $area,
            'loader_count' => $stat['loader_count'] ?? 0,
            'first_row_id' => $stat['first_row_id'] ?? null,
        ];
    }
}
