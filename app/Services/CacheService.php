<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

/**
 * CacheService — Centralized cache management for OneClick system.
 *
 * Define all cache keys and TTLs here for production performance.
 * Uses Redis when available (production), fallback to file/database cache (development).
 *
 * Cache Strategy:
 * - Employee list: 30 minutes (changes infrequently)
 * - Master categories: 1 hour (rarely change)
 * - Kesiapan operators summary: 15 minutes (frequently updated)
 * - SPDK lookups: 24 hours (external API results)
 * - Roles/permissions: 1 hour
 */
class CacheService
{
    // Cache Key Prefixes
    private const PREFIX_EMPLOYEES = 'oneclick:employees:';
    private const PREFIX_CATEGORIES = 'oneclick:categories:';
    private const PREFIX_KESIAPAN = 'oneclick:kesiapan:';
    private const PREFIX_SPDK = 'oneclick:spdk:';
    private const PREFIX_ROLES = 'oneclick:roles:';

    // Cache TTLs (in seconds)
    private const TTL_EMPLOYEES = 1800;      // 30 minutes
    private const TTL_CATEGORIES = 3600;     // 1 hour
    private const TTL_KESIAPAN = 900;        // 15 minutes
    private const TTL_SPDK = 86400;          // 24 hours
    private const TTL_ROLES = 3600;          // 1 hour

    /**
     * Get all active employees with eager loaded relationships.
     * Cached for 30 minutes.
     *
     * @return Collection
     */
    public function getEmployees(?string $departemen = null, ?string $status = null)
    {
        $cacheKey = self::PREFIX_EMPLOYEES . ($departemen ? "dept:{$departemen}:" : '') . ($status ? "status:{$status}" : 'all');

        return Cache::remember($cacheKey, self::TTL_EMPLOYEES, function () use ($departemen, $status) {
            $query = \App\Models\Employee::query()
                ->select(['id', 'name', 'nrp', 'departemen', 'status', 'email'])
                ->where('is_active', true);

            if ($departemen) {
                $query->where('departemen', $departemen);
            }

            if ($status) {
                $query->where('status', $status);
            }

            return $query->orderBy('name')->get();
        });
    }

    /**
     * Get employee by ID.
     * Cached for 30 minutes.
     *
     * @param int $employeeId
     * @return \App\Models\Employee|null
     */
    public function getEmployee(int $employeeId)
    {
        $cacheKey = self::PREFIX_EMPLOYEES . "id:{$employeeId}";

        return Cache::remember($cacheKey, self::TTL_EMPLOYEES, function () use ($employeeId) {
            return \App\Models\Employee::query()
                ->select(['id', 'name', 'nrp', 'departemen', 'status', 'email'])
                ->find($employeeId);
        });
    }

    /**
     * Get master categories.
     * Cached for 1 hour.
     *
     * @return Collection
     */
    public function getMasterCategories()
    {
        $cacheKey = self::PREFIX_CATEGORIES . 'all';

        return Cache::remember($cacheKey, self::TTL_CATEGORIES, function () {
            return \App\Models\MasterCategory::query()
                ->select(['id', 'name', 'code', 'type', 'is_active'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get master categories by type.
     * Cached for 1 hour.
     *
     * @param string $type
     * @return Collection
     */
    public function getMasterCategoriesByType(string $type)
    {
        $cacheKey = self::PREFIX_CATEGORIES . "type:{$type}";

        return Cache::remember($cacheKey, self::TTL_CATEGORIES, function () use ($type) {
            return \App\Models\MasterCategory::query()
                ->select(['id', 'name', 'code', 'type', 'is_active'])
                ->where('is_active', true)
                ->where('type', $type)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get kesiapan operators summary.
     * Cached for 15 minutes (frequently updated).
     *
     * @param string|null $tanggal (format: YYYY-MM-DD)
     * @return array
     */
    public function getKesiapanSummary(?string $tanggal = null)
    {
        $tanggal = $tanggal ?? date('Y-m-d');
        $cacheKey = self::PREFIX_KESIAPAN . "summary:{$tanggal}";

        return Cache::remember($cacheKey, self::TTL_KESIAPAN, function () use ($tanggal) {
            $data = \App\Models\MppKesiapanOperator::query()
                ->where('tanggal', $tanggal)
                ->select(['id', 'operator_id', 'status', 'tanggal'])
                ->get();

            return [
                'total' => $data->count(),
                'siap' => $data->where('status', 'siap')->count(),
                'tidak_siap' => $data->where('status', 'tidak_siap')->count(),
                'persentase_siap' => $data->count() > 0
                    ? round(($data->where('status', 'siap')->count() / $data->count()) * 100, 2)
                    : 0,
            ];
        });
    }

    /**
     * Get SPDK lookup data (cached from external API).
     * Cached for 24 hours.
     *
     * @param string $spdkCode
     * @return array|null
     */
    public function getSpdkData(string $spdkCode)
    {
        $cacheKey = self::PREFIX_SPDK . "code:{$spdkCode}";

        return Cache::remember($cacheKey, self::TTL_SPDK, function () use ($spdkCode) {
            // This would call external SPDK API in production
            // For now, return cached lookup from database
            return \App\Models\MasterSpdk::query()
                ->where('kode_spdk', $spdkCode)
                ->select(['id', 'kode_spdk', 'nama_spdk', 'deskripsi'])
                ->first()?->toArray();
        });
    }

    /**
     * Get user roles and permissions.
     * Cached for 1 hour.
     *
     * @param int $userId
     * @return array
     */
    public function getUserRoles(int $userId)
    {
        $cacheKey = self::PREFIX_ROLES . "user:{$userId}";

        return Cache::remember($cacheKey, self::TTL_ROLES, function () use ($userId) {
            $user = \App\Models\User::with(['roles', 'roles.permissions'])
                ->find($userId);

            if (!$user) {
                return ['roles' => [], 'permissions' => []];
            }

            return [
                'roles' => $user->roles->pluck('name')->toArray(),
                'permissions' => $user->roles
                    ->flatMap(fn ($role) => $role->permissions->pluck('name'))
                    ->unique()
                    ->toArray(),
            ];
        });
    }

    /**
     * Invalidate employee cache (call on create/update/delete).
     *
     * @param int|null $employeeId (if null, invalidate all employee caches)
     * @return void
     */
    public function invalidateEmployees(?int $employeeId = null)
    {
        if ($employeeId) {
            Cache::forget(self::PREFIX_EMPLOYEES . "id:{$employeeId}");
        }

        // Invalidate all employee-related caches
        Cache::tags(['employees'])->flush();
    }

    /**
     * Invalidate categories cache.
     *
     * @param string|null $type (if null, invalidate all category caches)
     * @return void
     */
    public function invalidateCategories(?string $type = null)
    {
        if ($type) {
            Cache::forget(self::PREFIX_CATEGORIES . "type:{$type}");
        } else {
            Cache::tags(['categories'])->flush();
        }
    }

    /**
     * Invalidate kesiapan operators cache.
     *
     * @param string|null $tanggal (format: YYYY-MM-DD)
     * @return void
     */
    public function invalidateKesiapan(?string $tanggal = null)
    {
        if ($tanggal) {
            Cache::forget(self::PREFIX_KESIAPAN . "summary:{$tanggal}");
        } else {
            Cache::tags(['kesiapan'])->flush();
        }
    }

    /**
     * Invalidate SPDK cache.
     *
     * @param string|null $spdkCode (if null, invalidate all SPDK caches)
     * @return void
     */
    public function invalidateSpdk(?string $spdkCode = null)
    {
        if ($spdkCode) {
            Cache::forget(self::PREFIX_SPDK . "code:{$spdkCode}");
        } else {
            Cache::tags(['spdk'])->flush();
        }
    }

    /**
     * Invalidate user roles cache.
     *
     * @param int $userId
     * @return void
     */
    public function invalidateUserRoles(int $userId)
    {
        Cache::forget(self::PREFIX_ROLES . "user:{$userId}");
    }

    /**
     * Clear ALL caches (use during emergency maintenance).
     *
     * @return void
     */
    public function clearAll()
    {
        Cache::flush();
    }
}
