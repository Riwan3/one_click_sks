<?php

namespace App\Http\Controllers;

use App\Http\Requests\RosterViewRequest;
use App\Services\RosterV2Service;
use App\Support\MenuAccess;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class OneRosterDashboardController extends Controller
{
    public function index(RosterViewRequest $request, RosterV2Service $service): Response
    {
        $date = $request->text('date', date('Y-m-d'));
        $jabatan = $request->text('jabatan');
        [$status, $dash] = $this->load($service, $date, $jabatan);

        return Inertia::render('OneRoster/Dashboard/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_roster_menu'),
            'status' => $status,
            'dash' => $dash,
            'filters' => [
                'date' => $dash['date'] ?? $date,
                'jabatan' => in_array($jabatan, $dash['jabatan_list'] ?? [], true) ? $jabatan : '',
            ],
            'today' => date('Y-m-d'),
        ]);
    }

    /** @return array{0: string, 1: array|null} [status, data]; status: ok | sections_missing | error */
    private function load(RosterV2Service $service, string $date, string $jabatan): array
    {
        try {
            if (! $service->sectionsReady()) {
                return ['sections_missing', null];
            }

            return ['ok', $service->getDashboard($date, $jabatan)];
        } catch (Throwable $e) {
            report($e);

            return ['error', null];
        }
    }
}
