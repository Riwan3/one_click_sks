<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersFleetPage;
use App\Http\Requests\ManpowerV201Request;
use App\Http\Resources\FleetDayViewResource;
use App\Services\FleetMasterService;
use App\Services\FleetSettingService;
use Inertia\Response;

/** One-Setting > Setting Fleet: halaman setting harian (per tanggal + shift). Aksi tulis di OneSettingFleetSettingController. */
class OneSettingFleetController extends Controller
{
    use RendersFleetPage;

    public function __construct(private FleetSettingService $setting, private FleetMasterService $master) {}

    public function index(ManpowerV201Request $request): Response
    {
        [$date, $shift] = [$request->workDate(), $request->workShift()];
        $load = $this->guarded($this->master->tablesReady(), fn () => [
            'view' => FleetDayViewResource::make($this->setting->view($date, $shift))->resolve(),
        ]);

        return $this->fleetPage('OneSetting/Fleet/Index', $load, ['filters' => compact('date', 'shift')]);
    }
}
