<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOdrIncidentActionRequest;
use App\Http\Requests\StoreOdrIncidentRequest;
use App\Http\Requests\UpdateOdrIncidentStatusRequest;
use App\Http\Resources\OdrIncidentResource;
use App\Models\OdrIncident;
use App\Models\OdrIncidentAction;
use App\Models\OdrIncidentEvidence;
use App\Services\OdrIncidentService;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/** Perbaikan Pasca-Insiden One-Reporting. */
class OdrIncidentController extends Controller
{
    public function __construct(private OdrIncidentService $incidentService) {}

    public function index(Request $request): Response
    {
        Gate::authorize('access-compliance-report');

        $filters = $this->incidentService->normalizeFilters($request->query());

        return Inertia::render('OneReporting/PerbaikanPascaInsiden', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_reporting_menu'),
            'filters' => $filters,
            'kpi' => $this->incidentService->kpi(),
            'incidents' => OdrIncidentResource::collection($this->incidentService->list($filters))->resolve(),
            'areaOptions' => $this->incidentService->areaOptions(),
        ]);
    }

    public function store(StoreOdrIncidentRequest $request): RedirectResponse
    {
        $incident = $this->incidentService->create($request->validated());

        return back()->with('success', "Data insiden \"{$incident->judul_insiden}\" berhasil ditambahkan.");
    }

    public function updateStatus(UpdateOdrIncidentStatusRequest $request, OdrIncident $incident): RedirectResponse
    {
        $this->incidentService->updateStatus($incident, $request->validated());

        return back()->with('success', 'Data insiden berhasil diperbarui.');
    }

    public function destroy(OdrIncident $incident): RedirectResponse
    {
        Gate::authorize('access-compliance-report');

        $this->incidentService->delete($incident);

        return back()->with('success', 'Data insiden berhasil dihapus.');
    }

    public function storeAction(StoreOdrIncidentActionRequest $request, OdrIncident $incident): RedirectResponse
    {
        $data = $request->safe()->only(['jenis', 'uraian_tindakan', 'pic']);
        $this->incidentService->addAction($incident, $data, $request->file('evidence', []), $this->actorName($request));

        return back()->with('success', 'Tindakan berhasil ditambahkan.');
    }

    public function destroyAction(OdrIncidentAction $action): RedirectResponse
    {
        Gate::authorize('access-compliance-report');

        $this->incidentService->deleteAction($action);

        return back()->with('success', 'Tindakan berhasil dihapus.');
    }

    public function storeEvidence(Request $request, OdrIncidentAction $action): RedirectResponse
    {
        Gate::authorize('access-compliance-report');
        $files = $request->validate(['evidence' => ['required', 'array'], 'evidence.*' => ['file', 'max:20480']])['evidence'];

        $uploaded = $this->incidentService->storeEvidence($action, $files, $this->actorName($request));

        return $uploaded > 0
            ? back()->with('success', "{$uploaded} evidence berhasil diunggah.")
            : back()->with('error', 'Tidak ada file yang berhasil diunggah (format tidak didukung).');
    }

    public function destroyEvidence(OdrIncidentEvidence $evidence): RedirectResponse
    {
        Gate::authorize('access-compliance-report');

        $this->incidentService->deleteEvidence($evidence);

        return back()->with('success', 'Evidence berhasil dihapus.');
    }

    private function actorName(Request $request): string
    {
        return (string) ($request->user()->username ?? $request->user()->name);
    }
}
