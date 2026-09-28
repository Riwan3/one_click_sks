<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveOdrMasterAktivitasRequest;
use App\Http\Requests\SaveOdrMasterAreaRequest;
use App\Http\Requests\SaveOdrMasterUnitRequest;
use App\Http\Resources\OdrMasterResource;
use App\Models\OdrMasterAktivitas;
use App\Models\OdrMasterArea;
use App\Models\OdrMasterUnit;
use App\Services\OdrMasterService;
use App\Support\MenuAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/** Master Data Compliance One-Reporting (tab Master Area, Unit/Lokasi, Aktivitas). */
class OdrMasterComplianceController extends Controller
{
    public function __construct(private OdrMasterService $masterService) {}

    public function index(Request $request): Response
    {
        Gate::authorize('access-compliance-report');

        $tab = in_array($request->query('tab'), OdrMasterService::TABS, true) ? $request->query('tab') : 'area';
        $rows = match ($tab) {
            'unit' => $this->masterService->units(),
            'aktivitas' => $this->masterService->aktivitas(),
            default => $this->masterService->areas(),
        };

        return Inertia::render('OneReporting/MasterCompliance', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_reporting_menu'),
            'tab' => $tab,
            'rows' => OdrMasterResource::collection($rows)->resolve(),
            'areaOptions' => $tab === 'unit' ? $this->masterService->activeAreaNames() : [],
            'kategoriOptions' => $tab === 'aktivitas' ? $this->masterService->kategoriOptions() : [],
            'documentOptions' => $tab === 'aktivitas' ? $this->masterService->documentOptions() : [],
        ]);
    }

    public function storeArea(SaveOdrMasterAreaRequest $request): RedirectResponse
    {
        $area = $this->masterService->saveArea($request->validated());

        return $this->backToTab('area', "Area \"{$area->nama}\" berhasil ditambahkan.");
    }

    public function updateArea(SaveOdrMasterAreaRequest $request, OdrMasterArea $area): RedirectResponse
    {
        $this->masterService->saveArea($request->validated(), $area);

        return $this->backToTab('area', 'Area berhasil diperbarui.');
    }

    public function destroyArea(OdrMasterArea $area): RedirectResponse
    {
        return $this->destroy($area, 'area', 'Area berhasil dihapus.');
    }

    public function storeUnit(SaveOdrMasterUnitRequest $request): RedirectResponse
    {
        $unit = $this->masterService->saveUnit($request->validated());

        return $this->backToTab('unit', "Unit \"{$unit->nama}\" berhasil ditambahkan.");
    }

    public function updateUnit(SaveOdrMasterUnitRequest $request, OdrMasterUnit $unit): RedirectResponse
    {
        $this->masterService->saveUnit($request->validated(), $unit);

        return $this->backToTab('unit', 'Unit berhasil diperbarui.');
    }

    public function destroyUnit(OdrMasterUnit $unit): RedirectResponse
    {
        return $this->destroy($unit, 'unit', 'Unit berhasil dihapus.');
    }

    public function storeAktivitas(SaveOdrMasterAktivitasRequest $request): RedirectResponse
    {
        $aktivitas = $this->masterService->saveAktivitas($request->validated());

        return $this->backToTab('aktivitas', "Aktivitas \"{$aktivitas->nama}\" berhasil ditambahkan.");
    }

    public function updateAktivitas(SaveOdrMasterAktivitasRequest $request, OdrMasterAktivitas $aktivitas): RedirectResponse
    {
        $this->masterService->saveAktivitas($request->validated(), $aktivitas);

        return $this->backToTab('aktivitas', 'Aktivitas berhasil diperbarui.');
    }

    public function destroyAktivitas(OdrMasterAktivitas $aktivitas): RedirectResponse
    {
        return $this->destroy($aktivitas, 'aktivitas', 'Aktivitas berhasil dihapus.');
    }

    private function destroy(Model $model, string $tab, string $message): RedirectResponse
    {
        Gate::authorize('access-compliance-report');

        $model->delete();

        return $this->backToTab($tab, $message);
    }

    private function backToTab(string $tab, string $message): RedirectResponse
    {
        return redirect()->route('one-reporting.master', ['tab' => $tab])->with('success', $message);
    }
}
