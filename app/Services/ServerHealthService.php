<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Kondisi server untuk OneClick Control Center. Setiap metrik bernilai
 * null bila tidak bisa dibaca di platform ini (mis. /proc di Windows) —
 * tidak pernah diisi angka karangan.
 *
 * score = 100 - pemakaian tertinggi di antara CPU, memori, dan disk (%),
 * jadi satu resource yang hampir penuh langsung menurunkan skor.
 */
class ServerHealthService
{
    /** @return array{score: int|null, cpu_pct: float|null, memory_pct: float|null, disk_pct: float|null, db_latency_ms: float|null} */
    public function snapshot(): array
    {
        $usage = [
            'cpu_pct' => $this->cpuPercent(),
            'memory_pct' => $this->memoryPercent(),
            'disk_pct' => $this->diskPercent(),
        ];

        $known = array_filter($usage, fn ($value) => $value !== null);

        return [
            'score' => $known ? (int) round(max(0, 100 - max($known))) : null,
            ...$usage,
            'db_latency_ms' => $this->dbLatencyMs(),
        ];
    }

    private function cpuPercent(): ?float
    {
        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : false;
        $cores = $this->cpuCores();

        return $load && $cores ? round(min(100, $load[0] / $cores * 100), 1) : null;
    }

    private function cpuCores(): ?int
    {
        $cpuinfo = @file_get_contents('/proc/cpuinfo');

        return $cpuinfo ? max(1, preg_match_all('/^processor\s*:/m', $cpuinfo)) : null;
    }

    private function memoryPercent(): ?float
    {
        $meminfo = @file_get_contents('/proc/meminfo');
        if (! $meminfo) {
            return null;
        }

        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);

        return isset($total[1], $available[1]) && $total[1] > 0
            ? round((1 - $available[1] / $total[1]) * 100, 1)
            : null;
    }

    private function diskPercent(): ?float
    {
        $total = @disk_total_space(base_path());
        $free = @disk_free_space(base_path());

        return $total && $free !== false ? round((1 - $free / $total) * 100, 1) : null;
    }

    private function dbLatencyMs(): ?float
    {
        try {
            $start = microtime(true);
            DB::select('select 1');

            return round((microtime(true) - $start) * 1000, 1);
        } catch (Throwable) {
            return null;
        }
    }
}
