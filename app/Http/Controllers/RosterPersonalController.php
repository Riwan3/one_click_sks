<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\RosterService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RosterPersonalController extends Controller
{
    private const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function __construct(private RosterService $rosterService) {}

    /**
     * Roster kalender 1 karyawan. Admin bebas pilih NRP manapun via ?nrp=,
     * non-admin dipaksa NRP miliknya sendiri (mengikuti V1: $isAdmin ? $_GET['nrp'] : $_SESSION['username']).
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $isAdmin = (bool) $user?->isAdmin();

        $month = (int) $request->integer('bulan', now()->month);
        $month = $month >= 1 && $month <= 12 ? $month : now()->month;
        $year = (int) $request->integer('tahun', now()->year);

        $selectedNrp = $isAdmin
            ? trim((string) $request->string('nrp'))
            : $this->ownNrp($user);

        $calendar = $selectedNrp
            ? $this->rosterService->personalCalendar($selectedNrp, $year, $month)
            : ['employee' => null, 'days' => []];

        return Inertia::render('OnePower/Roster/Personal', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'isAdmin' => $isAdmin,
            'filters' => [
                'nrp' => $selectedNrp,
                'bulan' => $month,
                'tahun' => $year,
                'monthNames' => self::MONTH_NAMES,
                'years' => range(now()->year - 2, now()->year + 1),
            ],
            'employee' => $calendar['employee'],
            'days' => $calendar['days'],
        ]);
    }

    /**
     * Autocomplete NRP/nama untuk picker admin. Non-admin tidak butuh ini
     * (tidak bisa pilih karyawan lain), tapi endpoint tetap role-gated untuk berjaga-jaga.
     */
    public function searchEmployee(Request $request): JsonResponse
    {
        abort_unless((bool) $request->user()?->isAdmin(), 403);

        $keyword = trim((string) $request->string('keyword'));

        if ($keyword === '') {
            return response()->json([]);
        }

        return response()->json(
            Employee::query()->search($keyword)->limit(10)->get(['id', 'nrp', 'name', 'jabatan_ss6', 'unit'])
        );
    }

    private function ownNrp($user): string
    {
        if (! $user) {
            return '';
        }

        return $user->employee?->nrp ?? $user->username ?? '';
    }
}
