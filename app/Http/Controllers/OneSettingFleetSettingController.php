<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMoncuErrors;
use App\Http\Requests\FleetInputRequest;
use App\Services\FleetAreaService;
use App\Services\FleetClusterService;
use App\Services\FleetSettingService;
use Illuminate\Http\RedirectResponse;

/** Aksi tulis Setting Fleet harian. Tanggal + shift selalu dari body; aturan bisnis di Service. */
class OneSettingFleetSettingController extends Controller
{
    use HandlesMoncuErrors;

    public function __construct(private FleetSettingService $setting, private FleetClusterService $clusters, private FleetAreaService $areas) {}

    public function storeArea(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->areas->add($request->workDate(), $request->workShift(), $request->text('area_name')));

        return back()->with('success', 'Area ditambahkan.');
    }

    public function updateArea(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->areas->rename($request->workDate(), $request->workShift(), $request->text('area_name'), $request->text('new_area_name')));

        return back()->with('success', 'Area diperbarui.');
    }

    public function destroyArea(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->areas->remove($request->workDate(), $request->workShift(), $request->text('area_name')));

        return back()->with('success', 'Area dihapus beserta cluster dan loader-nya.');
    }

    public function storeCluster(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->clusters->add($request->workDate(), $request->workShift(), $request->text('cluster_name'), $request->text('area_name')));

        return back()->with('success', 'Cluster ditambahkan.');
    }

    public function updateCluster(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->clusters->update(
            $request->workDate(), $request->workShift(), $request->text('cluster_name'), $request->text('new_cluster_name'), $request->text('area_name'),
        ));

        return back()->with('success', 'Cluster diperbarui.');
    }

    public function destroyCluster(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->clusters->remove($request->workDate(), $request->workShift(), $request->text('cluster_name')));

        return back()->with('success', 'Cluster dihapus beserta loader-nya.');
    }

    public function storeLoader(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->addLoader(
            $request->workDate(), $request->workShift(), $request->text('loader_code'), $request->text('cluster_name'), $request->loaderFields(),
        ));

        return back()->with('success', 'Loader ditambahkan.');
    }

    public function updateLoader(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->updateLoader(
            $request->workDate(), $request->workShift(), $request->text('loader_code'), $request->loaderFields(), $request->text('cluster_name'),
        ));

        return back()->with('success', 'Loader diperbarui.');
    }

    public function destroyLoader(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->removeLoader($request->workDate(), $request->workShift(), $request->text('loader_code')));

        return back()->with('success', 'Loader dihapus.');
    }

    public function storeHauler(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->assignHauler(
            $request->workDate(), $request->workShift(), $request->text('loader_code'), $request->text('hauler_code'),
        ));

        return back()->with('success', 'Hauler dipasang.');
    }

    public function moveHauler(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->moveHauler(
            $request->workDate(), $request->workShift(), $request->text('hauler_code'), $request->text('loader_code'),
        ));

        return back()->with('success', 'Hauler dipindahkan.');
    }

    public function destroyHauler(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->removeHauler($request->workDate(), $request->workShift(), $request->text('hauler_code')));

        return back()->with('success', 'Hauler dilepas.');
    }

    public function storeBreakdown(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->sendToBreakdown($request->workDate(), $request->workShift(), $request->text('unit_code')));

        return back()->with('success', 'Unit dikirim ke Breakdown/Service.');
    }

    public function destroyBreakdown(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->returnFromBreakdown($request->workDate(), $request->workShift(), $request->text('unit_code')));

        return back()->with('success', 'Unit dikembalikan ke Ready.');
    }

    public function copy(FleetInputRequest $request): RedirectResponse
    {
        $count = $this->guardPage(fn () => $this->setting->copy(
            $request->workDate(), $request->workShift(), $request->text('from_date'), strtoupper($request->text('from_shift', 'D')), $request->boolean('replace'),
        ));

        return back()->with('success', "Disalin: $count baris dari tanggal dan shift sumber.");
    }

    public function clear(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->clear($request->workDate(), $request->workShift()));

        return back()->with('success', 'Semua setting fleet pada tanggal dan shift ini dihapus.');
    }
}
