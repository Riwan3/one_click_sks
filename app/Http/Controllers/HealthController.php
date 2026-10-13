<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * Health Check Endpoint for Production Monitoring
 *
 * Endpoint: GET /health
 * Authentication: Public (no auth required)
 * Purpose: Used by monitoring systems (DataDog, Sentry, Uptime Robot) to verify system health
 *
 * Returns:
 * - 200: All systems operational
 * - 503: One or more critical systems down
 */
class HealthController extends Controller
{
    /**
     * Check overall system health.
     * Returns status of database, cache, queue, and other critical components.
     *
     * @return JsonResponse
     */
    public function check(): JsonResponse
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
            'services' => [],
            'metrics' => [],
        ];

        // Check Database Connection
        $health['services']['database'] = $this->checkDatabase();

        // Check Cache Connection
        $health['services']['cache'] = $this->checkCache();

        // Check Queue Connection (if configured)
        if (config('queue.default') !== 'sync') {
            $health['services']['queue'] = $this->checkQueue();
        }

        // Check Redis Connection (if configured)
        if (config('cache.default') === 'redis' || config('session.driver') === 'redis') {
            $health['services']['redis'] = $this->checkRedis();
        }

        // Overall status
        $health['status'] = collect($health['services'])
            ->every(fn ($service) => $service['status'] === 'operational')
            ? 'healthy'
            : 'degraded';

        // Collect metrics
        $health['metrics'] = [
            'uptime_seconds' => $this->getUptime(),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_limit_mb' => $this->getMemoryLimit(),
            'pending_jobs' => $this->getPendingJobsCount(),
            'database_connections' => DB::connection()->getPdo() ? 'active' : 'inactive',
        ];

        $statusCode = $health['status'] === 'healthy' ? 200 : 503;

        return response()->json($health, $statusCode);
    }

    /**
     * Check database connection.
     *
     * @return array
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            // Get connection details
            $version = DB::select('SELECT VERSION() as version')[0]->version ?? 'unknown';

            return [
                'status' => 'operational',
                'driver' => config('database.default'),
                'version' => $version,
                'response_time_ms' => $this->getResponseTime(function () {
                    DB::select('SELECT 1');
                }),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache connection.
     *
     * @return array
     */
    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'ok_' . microtime(true);

            Cache::put($testKey, $testValue, 60);
            $retrieved = Cache::get($testKey);

            if ($retrieved !== $testValue) {
                throw new \Exception('Cache value mismatch');
            }

            Cache::forget($testKey);

            return [
                'status' => 'operational',
                'driver' => config('cache.default'),
                'response_time_ms' => $this->getResponseTime(function () {
                    Cache::remember('health_check', 60, fn () => 'ok');
                }),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'down',
                'driver' => config('cache.default'),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue connection.
     *
     * @return array
     */
    private function checkQueue(): array
    {
        try {
            $driver = config('queue.default');

            // Try to ping the queue (simple connectivity check)
            if ($driver === 'database') {
                DB::connection()->getPdo();
            } elseif ($driver === 'redis') {
                Redis::ping();
            }

            return [
                'status' => 'operational',
                'driver' => $driver,
                'pending_jobs' => $this->getPendingJobsCount(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'down',
                'driver' => config('queue.default'),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check Redis connection (if configured).
     *
     * @return array
     */
    private function checkRedis(): array
    {
        try {
            $ping = Redis::ping();

            return [
                'status' => 'operational',
                'ping_response' => $ping,
                'response_time_ms' => $this->getResponseTime(function () {
                    Redis::ping();
                }),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get pending jobs count from queue.
     *
     * @return int
     */
    private function getPendingJobsCount(): int
    {
        try {
            $driver = config('queue.default');

            if ($driver === 'database') {
                // DatabaseJob is not an Eloquent model: count the queue table directly.
                return DB::table(config('queue.connections.database.table', 'jobs'))->count();
            } elseif ($driver === 'redis') {
                // Redis queue count (approximate)
                return Redis::llen(config('queue.connections.redis.queue', 'default')) ?? 0;
            }

            return 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Get server uptime in seconds (approximate).
     *
     * @return int
     */
    private function getUptime(): int
    {
        try {
            // exec() is disabled in production (docker/php.ini): read /proc/uptime instead.
            if (is_readable('/proc/uptime')) {
                return (int) explode(' ', (string) file_get_contents('/proc/uptime'))[0];
            }
        } catch (\Throwable) {
            // Fall through to the fallback below.
        }

        // Fallback: return PHP process uptime (less accurate)
        return (int) (microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true)));
    }

    /**
     * Get PHP memory limit in MB.
     *
     * @return int
     */
    private function getMemoryLimit(): int
    {
        $limit = ini_get('memory_limit');

        if ($limit === '-1') {
            return 0; // Unlimited
        }

        $bytes = (int) $limit;
        $bytes *= match (strtoupper(substr((string) $limit, -1))) {
            'G' => 1024 ** 3,
            'M' => 1024 ** 2,
            'K' => 1024,
            default => 1,
        };

        return (int) round($bytes / 1024 / 1024);
    }

    /**
     * Measure response time of a callable in milliseconds.
     *
     * @param callable $callable
     * @return float
     */
    private function getResponseTime(callable $callable): float
    {
        $start = microtime(true);
        $callable();
        $elapsed = microtime(true) - $start;

        return round($elapsed * 1000, 2); // Convert to milliseconds
    }
}
