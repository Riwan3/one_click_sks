<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Support\MenuAccess;
use Inertia\Inertia;
use Inertia\Response;

class MppDepartemenController extends Controller
{
    public function index(): Response
    {
        $departments = Employee::query()
            ->whereNotNull('unit')
            ->where('unit', '!=', '')
            ->select('unit')
            ->selectRaw("count(*) as total")
            ->selectRaw("sum(case when status_karyawan = 'FIT' then 1 else 0 end) as total_fit")
            ->groupBy('unit')
            ->orderBy('unit')
            ->get();

        return Inertia::render('OnePower/MppDepartemen/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => ['signature' => 2, 'notification' => 3, 'message' => 1],
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'departments' => $departments,
        ]);
    }
}
