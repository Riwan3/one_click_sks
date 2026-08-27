<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Support\MenuAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RosterDepartemenController extends Controller
{
    public function index(Request $request): Response
    {
        $departments = Employee::query()
            ->whereNotNull('unit')
            ->where('unit', '!=', '')
            ->select('unit')
            ->selectRaw('count(*) as total_karyawan')
            ->groupBy('unit')
            ->orderBy('unit')
            ->get();

        return Inertia::render('OnePower/RosterDepartemen/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'departments' => $departments,
        ]);
    }
}
