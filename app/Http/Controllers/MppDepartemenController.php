<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\MppSetting;
use App\Models\Unit;
use App\Support\MenuAccess;
use Database\Seeders\MppUnitSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MppDepartemenController extends Controller
{
    public function index(Request $request): Response
    {
        if (Unit::count() === 0) {
            (new MppUnitSeeder)->run();
        }

        $target1 = (float) MppSetting::get('mpp_target_1', '2.7');
        $target2 = (float) MppSetting::get('mpp_target_2', '2.8');

        $units = Unit::query()->orderBy('sort_order')->orderBy('id')->get();
        $staffUnits = $units->where('category_type', 'staff');
        $nonStaffUnits = $units->where('category_type', 'nonStaff');

        $staff = $staffUnits->values()->map(function (Unit $unit) {
            $actual = $this->countEmployeesForUnit($unit);
            $indexPengawas = (float) $unit->index_pengawas;
            $ach = $indexPengawas > 0 ? (int) round(($actual / $indexPengawas) * 100) : 0;

            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'index_pengawas' => $indexPengawas,
                'actual_mp' => $actual,
                'ach' => $ach,
            ];
        })->all();

        $nonStaff = $nonStaffUnits->values()->map(function (Unit $unit) use ($target1, $target2) {
            $actual = $this->countEmployeesForUnit($unit);
            $jmlUnit = (int) $unit->jumlah_unit;
            $mp1 = (int) round($jmlUnit * $target1);
            $mp2 = (int) round($jmlUnit * $target2);
            $deviasi = $actual - $mp2;
            $ach = $mp2 > 0 ? (int) round(($actual / $mp2) * 100) : 0;

            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'jumlah_unit' => $jmlUnit,
                'mp1' => $mp1,
                'actual_mp' => $actual,
                'mp2' => $mp2,
                'deviasi' => $deviasi,
                'ach' => $ach,
            ];
        })->all();

        return Inertia::render('OnePower/MppDepartemen/Index', [
            'user' => $this->currentUser(),
            'isAdmin' => (bool) ($request->user()?->isAdmin() ?? true),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'staff' => $staff,
            'nonStaff' => $nonStaff,
            'settings' => [
                'mpp_target_1' => (string) $target1,
                'mpp_target_2' => (string) $target2,
            ],
        ]);
    }

    public function updateTarget(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_1' => ['required', 'numeric', 'min:0', 'max:100'],
            'target_2' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        MppSetting::setMultiple([
            'mpp_target_1' => (string) $validated['target_1'],
            'mpp_target_2' => (string) $validated['target_2'],
        ]);

        return redirect()->route('one-power.mpp-departemen.analisis')
            ->with('success', 'Target MPP berhasil diperbarui.');
    }

    private function countEmployeesForUnit(Unit $unit): int
    {
        $rawPatterns = trim($unit->patterns ?: $unit->name);
        $patterns = array_filter(array_map('trim', explode(',', $rawPatterns)));

        if (empty($patterns)) {
            $patterns = [$unit->name];
        }

        return Employee::query()->where(function ($q) use ($patterns) {
            foreach ($patterns as $pattern) {
                $q->orWhere('jabatan_ss6', 'LIKE', "%{$pattern}%")
                  ->orWhere('jabatan_roster', 'LIKE', "%{$pattern}%")
                  ->orWhere('unit', 'LIKE', "%{$pattern}%");
            }
        })->count();
    }
}
