<?php

namespace App\Http\Controllers;

use App\Models\CoachingClinic;
use App\Models\CoachingMateri;
use App\Models\CoachingTahapan;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoachingClinicDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isManagement = $user?->isManagement() ?? false;

        $dateFrom = $request->query('date_from', now()->subMonths(12)->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->format('Y-m-d'));
        $department = $request->query('department');

        $query = CoachingClinic::query();

        if (!$isManagement) {
            $query->where('created_by', $user->id);
        }

        $query->whereBetween('tanggal', [$dateFrom, $dateTo]);

        if ($department) {
            $query->whereHas('employee', function ($q) use ($department) {
                $q->where('departemen', $department);
            });
        }

        $clinics = $query->get();

        // Calculate KPI metrics
        $totalClinics = $clinics->count();
        $activeCoaches = $clinics->pluck('coach_id')->unique()->filter()->count();
        $totalMaterials = CoachingMateri::count();
        $pendingReviews = CoachingMateri::whereHas('revisis', function ($q) {
            $q->where('status', 'pending');
        })->count();

        // Chart data: clinics per month
        $clinicsPerMonth = collect();
        $startDate = \Carbon\Carbon::parse($dateFrom);
        $endDate = \Carbon\Carbon::parse($dateTo);

        for ($date = $startDate->copy(); $date <= $endDate; $date->addMonth()) {
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            $count = $clinics->whereBetween('tanggal', [$monthStart, $monthEnd])->count();
            $clinicsPerMonth->push([
                'month' => $monthStart->format('Y-m'),
                'count' => $count,
            ]);
        }

        // Chart data: clinics by department
        $clinicsByDept = $clinics
            ->groupBy(fn ($c) => $c->employee?->departemen ?? 'Unknown')
            ->map(fn ($items) => $items->count())
            ->toArray();

        // Chart data: material categories
        $materialCategories = CoachingMateri::query()
            ->selectRaw('kategori, COUNT(*) as count')
            ->groupBy('kategori')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->kategori => $item->count])
            ->toArray();

        // Department list for filter dropdown
        $departments = Employee::distinct()
            ->whereNotNull('departemen')
            ->pluck('departemen')
            ->sort()
            ->values();

        return Inertia::render('CoachingClinic/Dashboard', [
            'kpis' => [
                'total_clinics' => $totalClinics,
                'active_coaches' => $activeCoaches,
                'total_materials' => $totalMaterials,
                'pending_reviews' => $pendingReviews,
            ],
            'charts' => [
                'clinics_per_month' => $clinicsPerMonth->toArray(),
                'clinics_by_department' => $clinicsByDept,
                'material_categories' => $materialCategories,
            ],
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'department' => $department,
                'departments' => $departments,
            ],
            'is_management' => $isManagement,
        ]);
    }
}

