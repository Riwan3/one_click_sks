<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\MppDailyAdjustment;
use App\Models\MppKesiapanOperator;
use App\Models\MppRosterData;
use App\Models\MppUnitPhoto;
use App\Support\MenuAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MppDetailController extends Controller
{
    public function dailyAtr(Request $request): Response
    {
        $this->authorize('view', MppDailyAdjustment::class);

        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'shift' => 'nullable|in:D,N',
            'kategori' => 'nullable|string',
        ]);

        $query = MppDailyAdjustment::query();

        if ($filters['date_from'] ?? false) {
            $query->where('tanggal', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] ?? false) {
            $query->where('tanggal', '<=', $filters['date_to']);
        }
        if ($filters['shift'] ?? false) {
            $query->where('shift', $filters['shift']);
        }
        if ($filters['kategori'] ?? false) {
            $query->where('kategori', $filters['kategori']);
        }

        $data = $query->latest('tanggal')->paginate(50);

        return Inertia::render('OnePower/MppDepartemen/DailyAtr', [
            'user' => $this->currentUser(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'dailyAtr' => $data,
            'filters' => $filters,
        ]);
    }

    public function actualMpp(Request $request): Response
    {
        $this->authorize('view', MppRosterData::class);

        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'shift' => 'nullable|in:D,N',
            'departemen' => 'nullable|string',
        ]);

        $query = MppRosterData::query();

        if ($filters['date_from'] ?? false) {
            $query->where('tanggal', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] ?? false) {
            $query->where('tanggal', '<=', $filters['date_to']);
        }
        if ($filters['shift'] ?? false) {
            $query->where('shift', $filters['shift']);
        }
        if ($filters['departemen'] ?? false) {
            $query->where('departemen', $filters['departemen']);
        }

        $data = $query->latest('tanggal')->paginate(50);

        return Inertia::render('OnePower/MppDepartemen/ActualMpp', [
            'user' => $this->currentUser(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'actualMpp' => $data,
            'filters' => $filters,
        ]);
    }

    public function checkpointData(Request $request): Response
    {
        $this->authorize('view', MppUnitPhoto::class);

        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'unit_code' => 'nullable|string',
            'shift' => 'nullable|in:D,N',
            'search' => 'nullable|string',
        ]);

        $query = MppUnitPhoto::byJenis('Checkpoint');

        if ($filters['date_from'] ?? false) {
            $query->where('tanggal', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] ?? false) {
            $query->where('tanggal', '<=', $filters['date_to']);
        }
        if ($filters['unit_code'] ?? false) {
            $query->byUnit($filters['unit_code']);
        }
        if ($filters['shift'] ?? false) {
            $query->byShift($filters['shift']);
        }
        if ($filters['search'] ?? false) {
            $query->search($filters['search']);
        }

        $photos = $query->latest('tanggal')->paginate(50);

        return Inertia::render('OnePower/MppDepartemen/CheckpointData', [
            'user' => $this->currentUser(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'photos' => $photos,
            'filters' => $filters,
        ]);
    }

    public function ojkData(Request $request): Response
    {
        $this->authorize('view', MppUnitPhoto::class);

        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'csa_code' => 'nullable|string',
            'shift' => 'nullable|in:D,N',
            'search' => 'nullable|string',
        ]);

        $query = MppUnitPhoto::byJenis('OJK');

        if ($filters['date_from'] ?? false) {
            $query->where('tanggal', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] ?? false) {
            $query->where('tanggal', '<=', $filters['date_to']);
        }
        if ($filters['csa_code'] ?? false) {
            $query->byUnit($filters['csa_code']);
        }
        if ($filters['shift'] ?? false) {
            $query->byShift($filters['shift']);
        }
        if ($filters['search'] ?? false) {
            $query->search($filters['search']);
        }

        $photos = $query->latest('tanggal')->paginate(50);

        return Inertia::render('OnePower/MppDepartemen/OjkData', [
            'user' => $this->currentUser(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'photos' => $photos,
            'filters' => $filters,
        ]);
    }

    public function kesiapanOperator(Request $request): Response
    {
        $this->authorize('view', MppKesiapanOperator::class);

        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'csa_code' => 'nullable|string',
        ]);

        $query = MppKesiapanOperator::query();

        if ($filters['date_from'] ?? false) {
            $query->where('tanggal', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] ?? false) {
            $query->where('tanggal', '<=', $filters['date_to']);
        }
        if ($filters['csa_code'] ?? false) {
            $query->where('csa_code', $filters['csa_code']);
        }

        $data = $query->latest('tanggal')->paginate(50);

        return Inertia::render('OnePower/MppDepartemen/KesiapanOperator', [
            'user' => $this->currentUser(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'kesiapanOperator' => $data,
            'filters' => $filters,
        ]);
    }

    public function showEmployees(Request $request): Response
    {
        $this->authorize('viewAny', Employee::class);

        $filters = $request->validate([
            'search' => 'nullable|string',
            'department' => 'nullable|string',
            'unit' => 'nullable|string',
            'shift' => 'nullable|in:D,N',
        ]);

        $query = Employee::query();

        if ($filters['search'] ?? false) {
            $query->where(fn ($q) =>
                $q->where('nrp', 'like', "%{$filters['search']}%")
                    ->orWhere('nama', 'like', "%{$filters['search']}%")
            );
        }
        if ($filters['department'] ?? false) {
            $query->where('departemen', $filters['department']);
        }
        if ($filters['unit'] ?? false) {
            $query->where('unit', $filters['unit']);
        }
        if ($filters['shift'] ?? false) {
            $query->where('shift', $filters['shift']);
        }

        $employees = $query->latest()->paginate(50);

        return Inertia::render('OnePower/MppDepartemen/ShowEmployees', [
            'user' => $this->currentUser(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'employees' => $employees,
            'filters' => $filters,
        ]);
    }

    public function showEmployeeDetail($employeeId): Response
    {
        $employee = Employee::findOrFail($employeeId);

        $this->authorize('view', $employee);

        $photos = MppUnitPhoto::where('nrp', $employee->nrp)
            ->latest('tanggal')
            ->get();

        $recentAtl = MppDailyAdjustment::query()
            ->where('tanggal', '>=', now()->subDays(30)->toDateString())
            ->latest('tanggal')
            ->get();

        return Inertia::render('OnePower/MppDepartemen/ShowEmployeeDetail', [
            'user' => $this->currentUser(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'employee' => $employee,
            'photos' => $photos,
            'recentAtl' => $recentAtl,
        ]);
    }
}
