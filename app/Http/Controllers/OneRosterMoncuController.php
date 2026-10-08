<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMoncuErrors;
use App\Http\Requests\MoncuPeriodRequest;
use App\Http\Requests\MoncuSheetCommitRequest;
use App\Http\Requests\MoncuSheetPreviewRequest;
use App\Http\Requests\PreviewMoncuPeriodRequest;
use App\Http\Requests\RosterViewRequest;
use App\Http\Requests\SaveMoncuEmployeeRequest;
use App\Http\Requests\SyncMoncuRosterRequest;
use App\Http\Resources\MoncuRowResource;
use App\Repositories\RosterV2Repository;
use App\Services\MoncuService;
use App\Services\RosterV2Service;
use App\Services\RosterV2SheetImportService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

/** One-Roster > MONCU (input cuti). Aturan bisnis ada di MoncuService (port V1). */
class OneRosterMoncuController extends Controller
{
    use HandlesMoncuErrors;

    private const PER_PAGE = 50;

    public function __construct(private MoncuService $moncu) {}

    public function index(RosterViewRequest $request, RosterV2Repository $rosterRepo): Response
    {
        $year = (int) ($request->text('year') ?: date('Y'));
        $year = ($year < 2000 || $year > 2100) ? (int) date('Y') : $year;
        $filters = [
            'q' => $request->text('q'),
            'group' => $request->text('group'),
            'section' => $request->text('section'),
        ];
        $canManage = MoncuService::canManage();
        $data = $this->moncu->getOverview($year, $filters, max(1, (int) $request->text('page', '1')), self::PER_PAGE);

        return Inertia::render('OneRoster/Moncu/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_roster_menu'),
            'year' => $year,
            'filters' => $filters,
            'rows' => MoncuRowResource::collection($data['rows'])->resolve(),
            'pagination' => Arr::only($data, ['total', 'page', 'pages', 'per_page']),
            'groups' => $data['groups'],
            'sections' => $data['sections'],
            'canManage' => $canManage,
            'moncuConfig' => Arr::only(config('moncu'), [
                'periods_per_year', 'period_months', 'max_tahunan', 'max_izin_resmi',
                'default_interval_days', 'entitlement_min_tenure_days',
            ]),
            'sheetRosters' => $canManage ? $this->activeRosters($rosterRepo) : [],
        ]);
    }

    public function storeEmployee(SaveMoncuEmployeeRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->moncu->saveEmployee($request->validated()));

        return back()->with('success', 'Karyawan MONCU disimpan.');
    }

    public function storePeriod(MoncuPeriodRequest $request): RedirectResponse
    {
        $in = $request->validated();
        $result = $this->guardPage(fn () => $this->moncu->savePeriod($in['nrp'], $in['year'], $in['period_no'], $in));

        return back()->with('success', trim('Periode cuti disimpan. '.implode(' ', $result['warnings'])));
    }

    public function destroyPeriod(MoncuPeriodRequest $request): RedirectResponse
    {
        $in = $request->validated();
        $result = $this->guardPage(fn () => $this->moncu->deletePeriod($in['nrp'], $in['year'], $in['period_no']));
        $reverted = (int) $result['sync']['reverted'];

        return back()->with('success', 'Periode cuti dihapus.'.($reverted > 0 ? " {$reverted} sel roster dikembalikan." : ''));
    }

    public function previewPeriod(PreviewMoncuPeriodRequest $request): JsonResponse
    {
        $in = $request->validated();

        return $this->guardJson(fn () => $this->moncu->previewPeriod($in['nrp'], $in['year'], $in['period_no'], $in));
    }

    public function searchEmployees(RosterViewRequest $request, RosterV2Service $rosterService): JsonResponse
    {
        return $this->guardJson(fn () => $rosterService->searchEmployees($request->text('q')));
    }

    /** Terapkan cuti aktual (CT/ID) ke roster draft; dipakai halaman Roster. */
    public function sync(SyncMoncuRosterRequest $request): JsonResponse
    {
        $in = $request->validated();
        $nrp = trim((string) ($in['nrp'] ?? '')) ?: null;

        return $this->guardJson(function () use ($in, $nrp) {
            $r = $this->moncu->syncRosterV2LeaveStatus($nrp, (int) $in['roster_id']);
            $r['message'] = "Sinkron cuti selesai: {$r['created']} dibuat, {$r['changed']} diubah, {$r['reverted']} dikembalikan.";
            $r['warnings'] = $this->syncWarnings($r);

            return $r;
        });
    }

    public function sheetPreview(MoncuSheetPreviewRequest $request, RosterV2SheetImportService $import): JsonResponse
    {
        set_time_limit(180);
        $in = $request->validated();

        return $this->guardJson(fn () => $import->previewMoncu($in['url'], (int) ($in['roster_id'] ?? 0)));
    }

    public function sheetCommit(MoncuSheetCommitRequest $request, RosterV2SheetImportService $import): JsonResponse
    {
        set_time_limit(180);
        $token = $request->validated()['token'];

        return $this->guardJson(fn () => $import->commitMoncu($token) + ['message' => 'Impor MONCU selesai.']);
    }

    /** Roster non-arsip untuk pilihan "Roster tujuan" pada import spreadsheet. */
    private function activeRosters(RosterV2Repository $rosterRepo): array
    {
        return collect($rosterRepo->listRosters([], 500))
            ->reject(fn ($r) => $r['status'] === 'archived')
            ->map(fn ($r) => Arr::only($r, ['id', 'name', 'status']))
            ->values()
            ->all();
    }

    private function syncWarnings(array $r): array
    {
        $warnings = [];
        $notInRoster = $r['not_in_roster'] ?? [];
        if ($notInRoster) {
            $warnings[] = count($notInRoster).' karyawan MONCU punya cuti pada periode ini tetapi belum ada di roster (dilewati, tidak ditambahkan): '
                .implode(', ', array_slice($notInRoster, 0, 10)).(count($notInRoster) > 10 ? ', ...' : '').'.';
        }
        if (! empty($r['kept'])) {
            $warnings[] = $r['kept'].' sel dipertahankan karena sudah punya alokasi unit.';
        }

        return $warnings;
    }
}
