<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AttendanceService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    private const PER_PAGE = 15;

    public function __construct(private AttendanceService $attendanceService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from')) ?: now()->startOfMonth()->format('Y-m-d');
        $dateTo = trim((string) $request->string('date_to')) ?: now()->format('Y-m-d');
        $page = max(1, (int) $request->integer('page', 1));

        $items = $this->attendanceService->paginate($search ?: null, $dateFrom, $dateTo, $page, self::PER_PAGE);

        return Inertia::render('OnePower/Attendance/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => ['signature' => 2, 'notification' => 3, 'message' => 1],
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'items' => AttendanceResource::collection($items->items())->resolve(),
            'pagination' => [
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        $this->attendanceService->create($request->validated());

        return redirect()->back()->with('success', 'Data absensi berhasil ditambahkan.');
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        $this->attendanceService->update($attendance, $request->validated());

        return redirect()->back()->with('success', 'Data absensi berhasil diperbarui.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $this->attendanceService->delete($attendance);

        return redirect()->back()->with('success', 'Data absensi berhasil dihapus.');
    }

    public function searchEmployee(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->string('keyword'));

        if ($keyword === '') {
            return response()->json([]);
        }

        $employees = Employee::query()->search($keyword)->limit(10)->get(['id', 'nrp', 'name', 'unit']);

        return response()->json($employees);
    }
}
