<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersFleetPage;
use App\Http\Requests\ManpowerV201Request;
use App\Http\Resources\FleetBoardResource;
use App\Http\Resources\FleetDayViewResource;
use App\Services\FleetBoardService;
use App\Services\FleetMasterService;
use App\Services\FleetSettingService;
use Inertia\Response;

/** One-Setting > Setting Fleet: halaman setting harian (per tanggal + shift). Aksi tulis di OneSettingFleetSettingController. */
class OneSettingFleetController extends Controller
{
    use RendersFleetPage;

    public function __construct(private FleetSettingService $setting, private FleetMasterService $master, private FleetBoardService $board) {}

    public function index(ManpowerV201Request $request): Response
    {
        [$date, $shift] = [$request->workDate(), $request->workShift()];
        $load = $this->guarded($this->master->tablesReady(), fn () => [
            'view' => FleetDayViewResource::make($this->setting->view($date, $shift))->resolve(),
        ]);

        return $this->fleetPage('OneSetting/Fleet/Index', $load, ['filters' => compact('date', 'shift')]);
    }

    /** Preview lembar fleet (sama dengan Papan Fleet di Setting Board) + unduh/salin gambar. */
    public function preview(ManpowerV201Request $request): Response
    {
        [$date, $shift] = [$request->workDate(), $request->workShift()];
        $load = $this->guarded($this->master->tablesReady(), function () use ($date, $shift) {
            $sheet = $this->board->sheet($date, $shift);

            return ['sheet' => $sheet ? FleetBoardResource::make($sheet)->resolve() : null];
        });

        return $this->fleetPage('OneSetting/Fleet/Preview', $load, ['filters' => compact('date', 'shift')]);
    }
}
