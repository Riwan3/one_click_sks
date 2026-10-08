<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMoncuErrors;
use App\Http\Controllers\Concerns\RendersFleetPage;
use App\Http\Requests\FleetMasterRequest;
use App\Http\Requests\ManpowerV201Request;
use App\Http\Resources\FleetMasterUnitResource;
use App\Models\FleetMasterUnit;
use App\Services\FleetMasterService;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

/** One-Setting > Setting Fleet > Master Unit: loader & hauler yang boleh dipakai di setting harian. */
class OneSettingFleetMasterController extends Controller
{
    use HandlesMoncuErrors;
    use RendersFleetPage;

    public function __construct(private FleetMasterService $master) {}

    public function index(ManpowerV201Request $request): Response
    {
        $load = $this->guarded($this->master->tablesReady(), fn () => [
            'loaders' => FleetMasterUnitResource::collection($this->master->listByType(FleetMasterUnit::LOADER))->resolve(),
            'haulers' => FleetMasterUnitResource::collection($this->master->listByType(FleetMasterUnit::HAULER))->resolve(),
            'groupOptions' => $this->master->groupOptions(),
            'pullPreview' => $this->master->pullPreview(),
        ]);

        return $this->fleetPage('OneSetting/Fleet/Master', $load);
    }

    public function store(FleetMasterRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->master->create($request->unitFields()));

        return back()->with('success', 'Unit ditambahkan.');
    }

    public function update(FleetMasterRequest $request, int $unit): RedirectResponse
    {
        $this->guardPage(fn () => $this->master->update($unit, $request->unitFields()));

        return back()->with('success', 'Unit diperbarui.');
    }

    public function active(FleetMasterRequest $request, int $unit): RedirectResponse
    {
        $isActive = $request->boolean('is_active');
        $this->guardPage(fn () => $this->master->setActive($unit, $isActive));

        return back()->with('success', $isActive ? 'Unit diaktifkan.' : 'Unit dinonaktifkan.');
    }

    public function destroy(FleetMasterRequest $request, int $unit): RedirectResponse
    {
        $this->guardPage(fn () => $this->master->delete($unit));

        return back()->with('success', 'Unit dihapus dari Master Unit.');
    }

    public function pull(FleetMasterRequest $request): RedirectResponse
    {
        $result = $this->guardPage(fn () => $this->master->pull($request->input('categories', [])));

        return back()->with('success', "Tarik dari Master Manpower: {$result['added']} unit ditambah, {$result['skipped']} dilewati (kode sudah ada).");
    }
}
