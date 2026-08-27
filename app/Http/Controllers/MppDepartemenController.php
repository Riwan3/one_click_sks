<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\MppDailyAdjustment;
use App\Models\MppMasterCategory;
use App\Models\MppSetting;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class MppDepartemenController extends Controller
{
    public function index(Request $request): Response
    {
        $tanggal = $request->date('tanggal') ?? now()->toDateString();
        $shift = in_array($request->query('shift'), ['D', 'N'], true) ? $request->query('shift') : 'D';

        $adjustments = MppDailyAdjustment::query()
            ->whereDate('tanggal', $tanggal)
            ->byShift($shift)
            ->get()
            ->keyBy('kategori');

        $categories = MppMasterCategory::active()->ordered()->get();

        $staff = $this->buildCategoryRows($categories->where('kategori_tipe', 'staff'), $adjustments);
        $pengawas = $this->buildCategoryRows($categories->where('kategori_tipe', 'pengawas'), $adjustments);

        $departments = Employee::query()
            ->whereNotNull('unit')
            ->where('unit', '!=', '')
            ->select('unit')
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when status_karyawan = 'FIT' then 1 else 0 end) as total_fit")
            ->groupBy('unit')
            ->orderBy('unit')
            ->get();

        return Inertia::render('OnePower/MppDepartemen/Index', [
            'user' => $this->currentUser(),
            'isAdmin' => $request->user()->isAdmin(),
            'notifCounts' => ['signature' => 2, 'notification' => 3, 'message' => 1],
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'departments' => $departments,
            'staff' => $staff,
            'pengawas' => $pengawas,
            'totals' => [
                'staff' => $this->buildTotalRow($staff),
                'pengawas' => $this->buildTotalRow($pengawas),
            ],
            'settings' => [
                'mpp_target_1' => MppSetting::get('mpp_target_1', '2.7'),
                'mpp_target_2' => MppSetting::get('mpp_target_2', '2.9'),
            ],
            'filters' => [
                'tanggal' => $tanggal,
                'shift' => $shift,
            ],
        ]);
    }

    public function updateTarget(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'target_1' => ['required', 'numeric', 'min:0', 'max:100'],
            'target_2' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        MppSetting::setMultiple([
            'mpp_target_1' => $validated['target_1'],
            'mpp_target_2' => $validated['target_2'],
        ]);

        return redirect()->route('one-power.mpp-departemen.analisis')
            ->with('success', 'Target MPP berhasil diperbarui.');
    }

    /**
     * @param  Collection<int, MppMasterCategory>  $categories
     * @param  Collection<string, MppDailyAdjustment>  $adjustments
     */
    private function buildCategoryRows(Collection $categories, Collection $adjustments): array
    {
        return $categories->values()->map(function (MppMasterCategory $category) use ($adjustments) {
            $adjustment = $adjustments->get($category->kode);

            $planMp = $category->plan_mp;
            $actualMp = $adjustment ? $adjustment->actual_mp : $planMp;
            $ach = $planMp > 0 ? round(($actualMp / $planMp) * 100) : 0;

            return [
                'id' => $category->id,
                'kode' => $category->kode,
                'nama' => $category->nama,
                'plan_mp' => $planMp,
                'actual_mp' => $actualMp,
                'ach' => $ach,
            ];
        })->all();
    }

    private function buildTotalRow(array $rows): array
    {
        return [
            'plan_mp' => array_sum(array_column($rows, 'plan_mp')),
            'actual_mp' => array_sum(array_column($rows, 'actual_mp')),
        ];
    }
}
