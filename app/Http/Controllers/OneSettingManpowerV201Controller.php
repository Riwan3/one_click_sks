<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMoncuErrors;
use App\Http\Requests\ManpowerV201Request;
use App\Http\Resources\ManpowerV201MasterResource;
use App\Http\Resources\ManpowerV201MonitoringRowResource;
use App\Http\Resources\ManpowerV201ViewResource;
use App\Services\ManpowerV201Service;
use App\Services\ManpowerV201SettingService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/** One-Setting > Settingan V2: halaman Setting Manpower v2.01 (harian, preview, master) dan monitoring (read-only). */
class OneSettingManpowerV201Controller extends Controller
{
    use HandlesMoncuErrors;

    public function __construct(
        private ManpowerV201SettingService $setting,
        private ManpowerV201Service $master,
    ) {}

    public function index(ManpowerV201Request $request): Response
    {
        return $this->dayPage('OneSetting/ManpowerV201/Harian', $request, fn ($date, $shift) => $this->setting->view($date, $shift));
    }

    public function preview(ManpowerV201Request $request): Response
    {
        return $this->dayPage('OneSetting/ManpowerV201/Preview', $request, fn ($date, $shift) => $this->setting->view($date, $shift), [
            'checkPointPassword' => fn ($date, $shift) => $this->setting->checkPointPassword($date, $shift),
        ]);
    }

    public function monitoringMp(ManpowerV201Request $request): Response
    {
        return $this->dayPage('OneSetting/ManpowerV201/MonitoringMp', $request, fn ($date, $shift) => $this->setting->viewWithCodes($date, $shift));
    }

    public function master(ManpowerV201Request $request): Response
    {
        $load = $this->guarded(fn () => ManpowerV201MasterResource::collection($this->master->tree())->resolve());

        return $this->render('OneSetting/ManpowerV201/Master', $load + ['tree' => $load['data'] ?? []]);
    }

    public function monitoring(ManpowerV201Request $request): Response
    {
        $status = $request->text('status');
        $load = $this->guarded(fn () => $this->setting->monitoring($request->month(), $status, $request->pageNumber()));
        $mon = $load['data'];

        return $this->render('OneSetting/ManpowerV201/Monitoring', $load + [
            'filters' => ['month' => $request->month(), 'status' => $status],
            'monitoring' => $mon ? [
                'rows' => ManpowerV201MonitoringRowResource::collection($mon['rows'])->resolve(),
                'counts' => $mon['counts'], 'pages' => $mon['pages'], 'page' => $mon['page'],
            ] : null,
        ]);
    }

    /** Kandidat modal Plot (JSON {success, data}). */
    public function candidates(ManpowerV201Request $request): JsonResponse
    {
        return $this->guardJson(fn () => $this->setting->candidatesFor(
            $request->workDate(), $request->workShift(), $request->requiredId('unit_id'),
        ));
    }

    /** Halaman per tanggal + shift: $view memuat model, $extras (opsional) data tambahan per tanggal + shift. */
    private function dayPage(string $component, ManpowerV201Request $request, callable $view, array $extras = []): Response
    {
        [$date, $shift] = [$request->workDate(), $request->workShift()];
        $load = $this->guarded(fn () => [
            'view' => ManpowerV201ViewResource::make($view($date, $shift))->resolve(),
            'extras' => array_map(fn ($extra) => $extra($date, $shift), $extras),
        ]);

        return $this->render($component, ['ready' => $load['ready'], 'loadError' => $load['loadError'], 'filters' => compact('date', 'shift')]
            + ['view' => $load['data']['view'] ?? null] + ($load['data']['extras'] ?? []));
    }

    /** Muat data; tabel belum ada / gagal baca -> ready=false + pesan teknis (seperti V1), bukan 500. */
    private function guarded(callable $load): array
    {
        if (! $this->setting->tablesReady()) {
            return ['ready' => false, 'loadError' => null, 'data' => null];
        }
        try {
            return ['ready' => true, 'loadError' => null, 'data' => $load()];
        } catch (Throwable $e) {
            report($e);

            return ['ready' => false, 'loadError' => $e->getMessage(), 'data' => null];
        }
    }

    private function render(string $component, array $props): Response
    {
        return Inertia::render($component, [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_setting_menu'),
        ] + collect($props)->except('data')->all());
    }
}
