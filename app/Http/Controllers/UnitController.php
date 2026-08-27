<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\MppSetting;
use App\Models\Unit;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function index(): Response
    {
        $settings = [
            'mpp_target_1' => (float) MppSetting::get('mpp_target_1', '2.7'),
            'mpp_target_2' => (float) MppSetting::get('mpp_target_2', '2.9'),
        ];

        $units = Unit::query()->orderBy('sort_order')->orderBy('name')->get();

        // Calculate actual counts from active employees
        $staffCountByPattern = [];
        $nonStaffCountByPattern = [];

        foreach ($units as $unit) {
            $pattern = trim($unit->patterns ?: $unit->name);
            $query = Employee::query()->where(function ($q) use ($pattern) {
                $q->where('jabatan', 'LIKE', "%{$pattern}%")
                  ->orWhere('jabatan_ss6', 'LIKE', "%{$pattern}%")
                  ->orWhere('unit', 'LIKE', "%{$pattern}%");
            });

            if ($unit->category_type === 'staff') {
                $staffCountByPattern[$unit->name] = $query->count();
            } else {
                $nonStaffCountByPattern[$unit->name] = $query->count();
            }
        }

        return Inertia::render('OneCore/Units/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_core_menu'),
            'units' => $units,
            'settings' => $settings,
            'mppData' => [
                'staff' => $staffCountByPattern,
                'nonStaff' => $nonStaffCountByPattern,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_type' => 'required|in:staff,nonStaff',
            'name' => 'required|string|max:255',
            'jumlah_unit' => 'nullable|numeric|min:0',
            'patterns' => 'nullable|string|max:255',
            'index_pengawas' => 'nullable|numeric|min:0',
        ]);

        Unit::create([
            'category_type' => $validated['category_type'],
            'name' => $validated['name'],
            'jumlah_unit' => (int) ($validated['jumlah_unit'] ?? 0),
            'patterns' => $validated['patterns'] ?? null,
            'index_pengawas' => (float) ($validated['index_pengawas'] ?? 0),
        ]);

        return redirect()->back()->with('success', 'Data unit berhasil ditambahkan.');
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'category_type' => 'required|in:staff,nonStaff',
            'name' => 'required|string|max:255',
            'jumlah_unit' => 'nullable|numeric|min:0',
            'patterns' => 'nullable|string|max:255',
            'index_pengawas' => 'nullable|numeric|min:0',
        ]);

        $unit->update([
            'category_type' => $validated['category_type'],
            'name' => $validated['name'],
            'jumlah_unit' => (int) ($validated['jumlah_unit'] ?? 0),
            'patterns' => $validated['patterns'] ?? null,
            'index_pengawas' => (float) ($validated['index_pengawas'] ?? 0),
        ]);

        return redirect()->back()->with('success', 'Data unit berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return redirect()->back()->with('success', 'Data unit berhasil dihapus.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_1' => 'required|numeric|min:0',
            'target_2' => 'required|numeric|min:0',
        ]);

        MppSetting::set('mpp_target_1', (string) $validated['target_1']);
        MppSetting::set('mpp_target_2', (string) $validated['target_2']);

        return redirect()->back()->with('success', 'Pengaturan target MPP global berhasil diperbarui.');
    }
}
