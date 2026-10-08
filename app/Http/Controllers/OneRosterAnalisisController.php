<?php

namespace App\Http\Controllers;

use App\Exceptions\RosterV2Conflict;
use App\Exceptions\RosterV2Forbidden;
use App\Exceptions\RosterV2NotFound;
use App\Http\Requests\RosterViewRequest;
use App\Http\Requests\SaveAnalysisTargetRequest;
use App\Repositories\RosterV2Repository;
use App\Services\RosterV2Service;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class OneRosterAnalisisController extends Controller
{
    private const REFRESH_DEFAULT = 15;

    public function __construct(private RosterV2Service $service, private RosterV2Repository $repository) {}

    public function index(RosterViewRequest $request): Response
    {
        [$status, $analysis, $targetsReady] = $this->load($request);

        return Inertia::render('OneRoster/Analisis/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_roster_menu'),
            'status' => $status,
            'analysis' => $analysis,
            'filters' => [
                'start' => $analysis['start'] ?? $request->text('start', date('Y-m-d')),
                'days' => $analysis['days'] ?? 25,
                'roster' => $analysis['roster_id'] ?? 0,
            ],
            'refreshSeconds' => max(3, min(120, (int) $request->query('refresh', self::REFRESH_DEFAULT))),
            'canManage' => (bool) $request->user()->canManageRosterV2(),
            'targetsReady' => $targetsReady,
        ]);
    }

    /** JSON untuk penyegaran otomatis grafik (sama dengan data halaman). */
    public function data(RosterViewRequest $request): JsonResponse
    {
        try {
            $data = $this->service->getAnalysis(
                $request->text('start', date('Y-m-d')),
                (int) $request->query('days', 25),
                (int) $request->query('roster', 0),
            );
        } catch (RosterV2Forbidden $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan pada server. Silakan coba lagi.'], 500);
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function target(SaveAnalysisTargetRequest $request): RedirectResponse
    {
        try {
            $this->service->setAnalysisTarget(
                $request->integer('roster_id'),
                $request->integer('section_id'),
                $request->input('target_siang', ''),
                $request->input('target_malam', ''),
            );
        } catch (RosterV2Forbidden $e) {
            abort(403, $e->getMessage());
        } catch (RosterV2NotFound $e) {
            abort(404, $e->getMessage());
        } catch (InvalidArgumentException|RosterV2Conflict $e) {
            return back()->withErrors(['target' => $e->getMessage()]);
        }

        return back()->with('success', 'Target analisis disimpan.');
    }

    /** @return array{0: string, 1: array|null, 2: bool} [status, data, targetsReady]; status: ok | sections_missing | error */
    private function load(RosterViewRequest $request): array
    {
        try {
            $targetsReady = $this->repository->analysisTargetsReady();
            if (! $this->service->sectionsReady()) {
                return ['sections_missing', null, $targetsReady];
            }
            $analysis = $this->service->getAnalysis(
                $request->text('start', date('Y-m-d')),
                (int) $request->query('days', 25),
                (int) $request->query('roster', 0),
            );

            return ['ok', $analysis, $targetsReady];
        } catch (Throwable $e) {
            report($e);

            return ['error', null, false];
        }
    }
}
