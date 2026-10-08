<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMoncuErrors;
use App\Http\Requests\FleetInputRequest;
use App\Services\FleetSettingService;
use Illuminate\Http\RedirectResponse;

/** Aksi tulis Setting Fleet harian. Tanggal + shift selalu dari body; aturan bisnis di Service. */
class OneSettingFleetSettingController extends Controller
{
    use HandlesMoncuErrors;

    public function __construct(private FleetSettingService $setting) {}

    public function storeLoader(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->addLoader(
            $request->workDate(), $request->workShift(), $request->text('loader_code'), $request->loaderFields(),
        ));

        return back()->with('success', 'Loader ditambahkan.');
    }

    public function updateLoader(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->updateLoader(
            $request->workDate(), $request->workShift(), $request->text('loader_code'), $request->loaderFields(),
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

    public function updateHauler(FleetInputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->setStatus(
            $request->workDate(), $request->workShift(), $request->text('hauler_code'), $request->text('status'),
        ));

        return back()->with('success', 'Status unit diubah.');
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
