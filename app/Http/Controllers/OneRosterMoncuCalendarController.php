<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMoncuErrors;
use App\Http\Requests\MoncuCalendarDayRequest;
use App\Http\Requests\RosterViewRequest;
use App\Services\MoncuCalendarService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

/** One-Roster > MONCU Calendar (READ-ONLY). */
class OneRosterMoncuCalendarController extends Controller
{
    use HandlesMoncuErrors;

    private const FILTER_KEYS = ['year', 'month', 'group', 'position', 'search'];

    public function __construct(private MoncuCalendarService $calendar) {}

    public function index(RosterViewRequest $request): Response
    {
        $input = [];
        foreach (self::FILTER_KEYS as $key) {
            $input[$key] = $request->text($key);
        }
        [$data, $hasParamError] = $this->loadMonth($input);

        return Inertia::render('OneRoster/MoncuCalendar/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_roster_menu'),
            'filters' => $data['filters'],
            'options' => $this->calendar->getFilterOptions(),
            'today' => $data['today'],
            'range' => $data['range'],
            'cells' => $data['cells'],
            'summary' => $data['summary'],
            'warnings' => $data['warnings'],
            'overlapNrp' => $data['overlap_nrp'],
            'state' => $data['state'],
            'hasParamError' => $hasParamError,
        ]);
    }

    public function day(MoncuCalendarDayRequest $request): JsonResponse
    {
        $in = $request->validated();
        $filters = array_intersect_key($in, array_flip(['group', 'position', 'search']));

        return $this->guardJson(fn () => $this->calendar->getDay(
            $in['date'],
            $filters,
            $in['section'] ?? null,
            (int) ($in['page'] ?? 1),
            (int) ($in['per_page'] ?? MoncuCalendarService::PER_PAGE_DEFAULT),
        ));
    }

    /** year/month tidak valid -> kembali ke bulan berjalan + pesan (seperti V1). */
    private function loadMonth(array $input): array
    {
        try {
            return [$this->calendar->getMonth($input), false];
        } catch (InvalidArgumentException) {
            return [$this->calendar->getMonth(array_diff_key($input, array_flip(['year', 'month']))), true];
        }
    }
}
