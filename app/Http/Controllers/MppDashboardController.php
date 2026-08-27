<?php

namespace App\Http\Controllers;

use App\Models\MppDailyAdjustment;
use App\Models\MppKesiapanOperator;
use App\Models\MppMasterCategory;
use App\Models\MppUnitPhoto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MppDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        abort_if(! in_array($user->role, ['admin', 'pimpinan', 'gl_manpower', 'operator']), 403);

        $todayCount = MppDailyAdjustment::whereDate('tanggal', Carbon::today())->count();
        $recentPhotos = MppUnitPhoto::latest()->limit(6)->get();
        $categories = MppMasterCategory::active()->ordered()->get();

        // Role-based dashboard pages visibility
        $pages = $this->getPagesForRole($user->role);

        return Inertia::render('MppDepartemen/Dashboard', [
            'todayCount' => $todayCount,
            'recentPhotos' => $recentPhotos,
            'categories' => $categories,
            'pages' => $pages,
            'userRole' => $user->role,
        ]);
    }

    private function getPagesForRole(string $role): array
    {
        $allPages = [
            ['id' => 'master-categories', 'title' => 'Master Kategori', 'description' => 'Kelola kategori MPP', 'icon' => 'layout-grid'],
            ['id' => 'daily-atr', 'title' => 'Daily ATR', 'description' => 'Penyesuaian harian', 'icon' => 'calendar'],
            ['id' => 'actual-mpp', 'title' => 'Actual MPP', 'description' => 'Realisasi MPP', 'icon' => 'chart-line'],
            ['id' => 'checkpoint', 'title' => 'Checkpoint', 'description' => 'Foto Checkpoint', 'icon' => 'camera'],
            ['id' => 'ojk', 'title' => 'OJK', 'description' => 'Foto OJK', 'icon' => 'image'],
            ['id' => 'employees', 'title' => 'Karyawan', 'description' => 'Detail karyawan', 'icon' => 'users'],
            ['id' => 'photo-upload', 'title' => 'Upload Foto', 'description' => 'Unggah foto bukti', 'icon' => 'upload-cloud'],
            ['id' => 'kesiapan-operators', 'title' => 'Kesiapan Operator', 'description' => 'Equipment readiness', 'icon' => 'zap'],
            ['id' => 'reports', 'title' => 'Laporan', 'description' => 'Export & analisa', 'icon' => 'file-text'],
        ];

        return match ($role) {
            'admin' => $allPages,
            'pimpinan' => $allPages,
            'gl_manpower' => $this->filterPagesByDepartment($allPages, ['master-categories', 'daily-atr', 'actual-mpp', 'photo-upload']),
            'operator' => $this->filterPagesByDepartment($allPages, ['photo-upload', 'actual-mpp']),
            default => [],
        };
    }

    private function filterPagesByDepartment(array $pages, array $allowedIds): array
    {
        return array_filter($pages, fn ($page) => in_array($page['id'], $allowedIds));
    }
}
