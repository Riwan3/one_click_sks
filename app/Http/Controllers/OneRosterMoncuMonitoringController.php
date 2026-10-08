<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMoncuErrors;
use App\Http\Requests\MoncuMonitoringDetailRequest;
use App\Http\Requests\RosterViewRequest;
use App\Http\Resources\MoncuMonitoringRowResource;
use App\Services\MoncuMonitoringService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

/** One-Roster > MONCU Monitoring (READ-ONLY). */
class OneRosterMoncuMonitoringController extends Controller
{
    use HandlesMoncuErrors;

    private const PER_PAGE = 25;

    public function __construct(private MoncuMonitoringService $monitoring) {}

    public function index(RosterViewRequest $request): Response
    {
        $input = [];
        foreach (['year', 'group', 'jabatan', 'status', 'q'] as $key) {
            $input[$key] = $request->text($key);
        }
        $overview = $this->monitoring->getOverview(
            $input,
            $request->text('sort', 'status'),
            $request->text('dir', 'asc'),
            max(1, (int) $request->text('page', '1')),
            self::PER_PAGE,
        );
        $table = $overview['table'];

        return Inertia::render('OneRoster/MoncuMonitoring/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_roster_menu'),
            'filters' => Arr::only($overview['filters'], ['year', 'group', 'jabatan', 'status', 'q']),
            'options' => $this->monitoring->getFilterOptions(),
            'today' => $overview['today'],
            'context' => $overview['context'],
            'summary' => $overview['summary'],
            'attentionApplicable' => $overview['attention_applicable'],
            'attention' => collect($overview['attention'])->map(fn ($a, $key) => ['key' => $key] + $a)->values(),
            'rows' => MoncuMonitoringRowResource::collection($table['rows'])->resolve(),
            'table' => Arr::only($table, ['total', 'page', 'pages', 'per_page', 'sort', 'dir']),
        ]);
    }

    public function detail(MoncuMonitoringDetailRequest $request): JsonResponse
    {
        $in = $request->validated();

        return $this->guardJson(fn () => $this->monitoring->getEmployeeDetail($in['nrp'], $in['year'] ?? null));
    }
}
