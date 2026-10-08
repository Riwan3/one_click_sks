<?php

namespace App\Http\Controllers\Concerns;

use App\Support\MenuAccess;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/** Render halaman Setting Fleet dengan props standar + pembacaan data yang tidak melempar 500 (ready/loadError). */
trait RendersFleetPage
{
    /** Muat data; tabel belum ada / gagal baca -> ready=false + pesan teknis, bukan 500. */
    protected function guarded(bool $isReady, callable $load): array
    {
        if (! $isReady) return ['ready' => false, 'loadError' => null, 'data' => []];

        try {
            return ['ready' => true, 'loadError' => null, 'data' => $load()];
        } catch (Throwable $e) {
            report($e);

            return ['ready' => false, 'loadError' => $e->getMessage(), 'data' => []];
        }
    }

    protected function fleetPage(string $component, array $load, array $props = []): Response
    {
        return Inertia::render($component, [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_setting_menu'),
            'ready' => $load['ready'],
            'loadError' => $load['loadError'],
        ] + $props + $load['data']);
    }
}
