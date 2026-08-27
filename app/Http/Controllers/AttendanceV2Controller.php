<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttendanceV2DetailResource;
use App\Models\AttendanceV2Detail;
use App\Services\AttendanceV2Service;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceV2Controller extends Controller
{
    private const PER_PAGE = 15;

    public function __construct(private AttendanceV2Service $attendanceV2Service) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from')) ?: now()->startOfMonth()->format('Y-m-d');
        $dateTo = trim((string) $request->string('date_to')) ?: now()->format('Y-m-d');
        $category = trim((string) $request->string('category'));
        $page = max(1, (int) $request->integer('page', 1));

        $attendanceData = $this->attendanceV2Service->paginate($search, $dateFrom, $dateTo, $category, $page, self::PER_PAGE);
        $summary = $this->attendanceV2Service->summary($search, $dateFrom, $dateTo);

        return Inertia::render('OnePower/PersonalData/AttendanceV2/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'category' => $category,
            'summary' => $summary,
            'attendanceData' => AttendanceV2DetailResource::collection($attendanceData->items())->resolve(),
            'pagination' => [
                'currentPage' => $attendanceData->currentPage(),
                'lastPage' => $attendanceData->lastPage(),
                'total' => $attendanceData->total(),
            ],
        ]);
    }

    public function importFromApi(Request $request): RedirectResponse
    {
        $request->validate([
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'min:2000'],
            'dept' => ['nullable', 'string', 'max:50'],
        ]);

        $dept = $request->string('dept')->trim()->toString() ?: 'PRO';

        $result = $this->attendanceV2Service->importFromApi(
            $request->integer('bulan'),
            $request->integer('tahun'),
            $dept
        );

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect('/one-power/personal-data/attendance-v2')->with('success', $result['message']);
    }

    public function destroy(AttendanceV2Detail $attendanceV2): RedirectResponse
    {
        $this->attendanceV2Service->delete($attendanceV2);

        return redirect()->back()->with('success', 'Data Attendance V2 berhasil dihapus.');
    }
}
