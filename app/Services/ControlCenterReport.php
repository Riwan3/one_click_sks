<?php

namespace App\Services;

use App\Models\LoginLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Data yang dilaporkan ke OneClick Control Center: status & kondisi server,
 * aktivitas login, dan struktur menu modul. Dipakai bersama oleh endpoint
 * baca-saja (/api/cc/*, mode pull) dan command cc:push (mode push), supaya
 * isinya selalu sama.
 */
class ControlCenterReport
{
    private const MENU_CONFIGS = [
        'one-power' => 'one_power_menu',
        'one-academy' => 'one_academy_menu',
        'one-core' => 'one_core_menu',
        'one-dms' => 'one_dms_menu',
        'one-gmp' => 'one_gmp_menu',
        'one-reporting' => 'one_reporting_menu',
        'one-setting' => 'one_setting_menu',
        'one-procedure' => 'one_procedure_menu',
        'one-roster' => 'one_roster_menu',
    ];

    private const RECENT_LOGINS = 8;

    private const TREND_DAYS = 7;

    public function __construct(private ServerHealthService $health)
    {
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return [
            'status' => $this->status(),
            'activity' => $this->activity(),
            'menus' => $this->menus(),
        ];
    }

    /** @return array<string, mixed> */
    public function status(): array
    {
        $databaseOk = $this->databaseOk();

        return [
            'status' => $databaseOk ? 'online' : 'warning',
            'version' => config('services.control_center.version'),
            'environment' => app()->environment(),
            'deployed_at' => $this->deployedAt(),
            'users' => $databaseOk ? User::count() : null,
            'modules' => collect(config('modules.list'))->pluck('key')->all(),
            'health' => $this->health->snapshot(),
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /** Login harian 7 hari terakhir + login terbaru, dari login_logs. @return array<string, mixed> */
    public function activity(): array
    {
        $since = CarbonImmutable::today()->subDays(self::TREND_DAYS - 1);

        $successful = LoginLog::where('status', 'SUCCESS')->where('login_at', '>=', $since)
            ->get(['username', 'login_at'])
            ->groupBy(fn (LoginLog $log) => $log->login_at->toDateString());

        $daily = collect(range(0, self::TREND_DAYS - 1))->map(function (int $offset) use ($since, $successful) {
            $date = $since->addDays($offset);
            $logs = $successful->get($date->toDateString(), collect());

            return [
                'date' => $date->toDateString(),
                'label' => $date->format('D'),
                'logins' => $logs->count(),
                'unique_users' => $logs->pluck('username')->unique()->count(),
            ];
        })->all();

        $recent = LoginLog::latest('login_at')->limit(self::RECENT_LOGINS)
            ->get(['username', 'nama', 'status', 'login_at'])
            ->map(fn (LoginLog $log) => [
                'occurred_at' => $log->login_at?->toIso8601String(),
                'title' => $log->status === 'SUCCESS' ? 'Login berhasil' : 'Login gagal',
                'description' => ($log->nama ?: $log->username).' ('.$log->status.')',
                'type' => $log->status === 'SUCCESS' ? 'activity' : 'system',
            ])->all();

        return [
            'active_users_today' => end($daily)['unique_users'],
            'daily' => $daily,
            'recent' => $recent,
        ];
    }

    /** Struktur menu sidebar tiap modul, diratakan (id, label, parentId, order, route). @return array<string, mixed> */
    public function menus(): array
    {
        return ['modules' => collect(self::MENU_CONFIGS)->map(fn (string $config) => [
            'version' => config('services.control_center.version'),
            'menus' => $this->flattenMenus(config($config, [])),
        ])->all()];
    }

    private function flattenMenus(array $items, ?string $parentId = null): array
    {
        $flat = [];

        foreach (array_values($items) as $index => $item) {
            $flat[] = [
                'id' => $item['menu_key'],
                'label' => $item['label'],
                'parentId' => $parentId,
                'order' => $index + 1,
                'route' => $item['href'] ?? null,
            ];

            array_push($flat, ...$this->flattenMenus($item['children'] ?? [], $item['menu_key']));
        }

        return $flat;
    }

    private function deployedAt(): ?string
    {
        $configured = config('services.control_center.deployed_at');
        if ($configured) {
            return CarbonImmutable::parse($configured)->toIso8601String();
        }

        $built = @filemtime(public_path('build/manifest.json'));

        return $built ? CarbonImmutable::createFromTimestamp($built)->toIso8601String() : null;
    }

    private function databaseOk(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
