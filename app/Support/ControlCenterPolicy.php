<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Konfigurasi yang dikirim OneClick Control Center ke site ini (balasan
 * dari cc:push): modul dan menu yang dinonaktifkan, serta versi target.
 *
 * Aman secara desain:
 *  - Hanya DISEMBUNYIKAN dari tampilan (modul di portal, menu di sidebar);
 *    ini bukan kontrol akses — URL langsung tetap tidak diblokir.
 *  - Baru berlaku bila CONTROL_CENTER_APPLY_CONFIG=true (default mati).
 *  - Gagal baca / belum pernah sinkron = semua tetap tampil.
 *  - Modul "one-core" (tempat admin mengatur role & user) tidak pernah
 *    bisa disembunyikan, supaya admin site tidak terkunci.
 */
class ControlCenterPolicy
{
    private const CACHE_KEY = 'cc.policy';

    private const PROTECTED_MODULES = ['one-core'];

    public static function store(array $config): void
    {
        try {
            Cache::forever(self::CACHE_KEY, [...$config, 'synced_at' => now()->toIso8601String()]);
        } catch (Throwable) {
            // Cache tidak tersedia: abaikan, tampilan tetap utuh.
        }
    }

    public static function applying(): bool
    {
        return (bool) config('services.control_center.apply_config');
    }

    public static function moduleEnabled(string $moduleKey): bool
    {
        if (! self::applying() || in_array($moduleKey, self::PROTECTED_MODULES, true)) {
            return true;
        }

        return ! in_array($moduleKey, self::current()['disabled_modules'] ?? [], true);
    }

    /** Buang menu yang dinonaktifkan Control Center dari tree sidebar sebuah modul. */
    public static function filterMenus(string $moduleKey, array $tree): array
    {
        if (! self::applying()) {
            return $tree;
        }

        $disabled = collect(self::current()['disabled_menus'][$moduleKey] ?? [])
            ->map(fn ($label) => self::slug((string) $label))
            ->all();

        return $disabled === [] ? $tree : self::filterItems($tree, $disabled);
    }

    /** Ringkasan untuk dilaporkan balik ke Control Center. @return array<string, mixed> */
    public static function syncInfo(): array
    {
        $policy = self::current();

        return [
            'apply' => self::applying(),
            'synced_at' => $policy['synced_at'] ?? null,
            'target_version' => $policy['target_version'] ?? null,
            'disabled_modules' => count($policy['disabled_modules'] ?? []),
            'disabled_menus' => array_sum(array_map('count', $policy['disabled_menus'] ?? [])),
            'unmapped_menus' => $policy['unmapped_menus'] ?? 0,
        ];
    }

    /** @return array<string, mixed> */
    private static function current(): array
    {
        try {
            return (array) Cache::get(self::CACHE_KEY, []);
        } catch (Throwable) {
            return [];
        }
    }

    private static function filterItems(array $items, array $disabled): array
    {
        $result = [];

        foreach ($items as $item) {
            if (isset($item['children'])) {
                $children = self::filterItems($item['children'], $disabled);

                // Induk yang semua anaknya dinonaktifkan ikut disembunyikan.
                if ($children !== [] || $item['children'] === []) {
                    $result[] = [...$item, 'children' => $children];
                }

                continue;
            }

            if (! in_array(self::slug($item['label'] ?? ''), $disabled, true)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    private static function slug(string $label): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower($label));
    }
}
