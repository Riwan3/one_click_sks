<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\FleetAllocation;
use App\Models\RosterData;
use App\Models\Setting;
use App\Models\Unit;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OneSettingController extends Controller
{
    protected function currentUser(): array
    {
        $u = auth()->user();
        if ($u) {
            return parent::currentUser();
        }

        return [
            'displayName' => 'Administrator',
            'username' => 'admin',
            'name' => 'Administrator',
            'role' => 'Admin',
            'email' => 'admin@oneclick.id',
            'jabatan' => 'Administrator',
            'unit' => 'HO',
            'foto' => null,
        ];
    }

    /**
     * Dashboard One-Setting
     */
    public function dashboard(Request $request): Response
    {
        $date = $request->query('date', date('Y-m-d'));
        $shift = $request->query('shift', 'D');

        $totalManpowerRoster = RosterData::where('tanggal', $date)->count();
        $totalFleetUnits = Unit::count();
        $totalFleetAllocations = FleetAllocation::where('tanggal', $date)->count();
        $totalEmployees = Employee::count();

        $recentRoster = RosterData::where('tanggal', $date)
            ->where('kode', $shift)
            ->take(10)
            ->get();

        return Inertia::render('OneSetting/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_setting_menu'),
            'filters' => ['date' => $date, 'shift' => $shift],
            'stats' => [
                'totalManpowerRoster' => $totalManpowerRoster,
                'totalFleetUnits' => $totalFleetUnits,
                'totalFleetAllocations' => $totalFleetAllocations,
                'totalEmployees' => $totalEmployees,
            ],
            'recentRoster' => $recentRoster,
        ]);
    }

    /**
     * Halaman Settingan:
     * - Input Setting Manpower
     * - Input Setting Fleet
     * - Data Alokasi Manpower
     */
    public function settingan(Request $request): Response
    {
        $activeTab = $request->query('sub', 'manpower');
        $date = $request->query('date', date('Y-m-d'));
        $shift = $request->query('shift', 'D');

        // GSheet Manpower settings
        $gsheetManpowerDay = Setting::getValue('google_sheet_manpower_url_day_' . $date) 
            ?: Setting::getValue('google_sheet_manpower_url_day', '');
        $gsheetManpowerNight = Setting::getValue('google_sheet_manpower_url_night_' . $date) 
            ?: Setting::getValue('google_sheet_manpower_url_night', '');

        // GSheet Fleet settings
        $gsheetFleetDay = Setting::getValue('fleet_spreadsheet_url_day', '');
        $gsheetFleetNight = Setting::getValue('fleet_spreadsheet_url_night', '');

        // Fetch Roster Data for Data Alokasi
        $rosterData = RosterData::where('tanggal', $date)
            ->where('kode', $shift)
            ->orderBy('unit', 'asc')
            ->get()
            ->map(function ($r) {
                $emp = Employee::where('nrp', $r->nrp)->first();
                return [
                    'id' => $r->id,
                    'nrp' => $r->nrp,
                    'tanggal' => $r->tanggal,
                    'unit' => $r->unit,
                    'kode' => $r->kode,
                    'csa_type' => $r->csa_type,
                    'employee_name' => $emp ? $emp->name : 'NRP: ' . $r->nrp,
                    'phone' => $emp ? $emp->no_telepon : '-',
                ];
            });

        // Duplicate Detection
        $unitCounts = [];
        $nrpCounts = [];
        $excludeKw = ['SPARE', '-', 'OFF', 'STANDBY', 'ALL DISPOSAL', 'ALL FRONT', 'COAL', 'DISPOSAL', 'FRONT', 'HRM', 'MANPOWER', 'PSV', 'MCU', 'TR', 'ID', 'IDS'];

        foreach ($rosterData as $r) {
            $u = strtoupper(trim($r['unit']));
            $n = trim($r['nrp']);

            $isExcluded = false;
            foreach ($excludeKw as $kw) {
                if (str_contains($u, $kw)) {
                    $isExcluded = true;
                    break;
                }
            }

            if ($u && !$isExcluded) {
                $unitCounts[$u] = ($unitCounts[$u] ?? 0) + 1;
            }
            $nrpCounts[$n] = ($nrpCounts[$n] ?? 0) + 1;
        }

        $conflicts = [
            'units' => array_keys(array_filter($unitCounts, fn($c) => $c > 1)),
            'nrps' => array_keys(array_filter($nrpCounts, fn($c) => $c > 1)),
        ];

        // Fetch Fleet Master Units
        $fleetUnits = Unit::orderBy('category_type', 'desc')->orderBy('name', 'asc')->get();

        return Inertia::render('OneSetting/Settingan', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_setting_menu'),
            'activeTab' => $activeTab,
            'filters' => ['date' => $date, 'shift' => $shift],
            'gsheetManpower' => ['day' => $gsheetManpowerDay, 'night' => $gsheetManpowerNight],
            'gsheetFleet' => ['day' => $gsheetFleetDay, 'night' => $gsheetFleetNight],
            'rosterData' => $rosterData,
            'conflicts' => $conflicts,
            'fleetUnits' => $fleetUnits,
        ]);
    }

    /**
     * Save Manual Roster Entry
     */
    public function storeManualRoster(Request $request): RedirectResponse
    {
        $request->validate([
            'nrp' => 'required|string',
            'unit' => 'required|string',
            'tanggal' => 'required|date',
            'shift' => 'required|string',
        ]);

        RosterData::updateOrCreate(
            ['nrp' => $request->nrp, 'tanggal' => $request->tanggal, 'kode' => $request->shift],
            ['unit' => strtoupper(trim($request->unit)), 'csa_type' => $request->csa_type ?? 'CSA GENERAL']
        );

        return back()->with('success', 'Roster manual berhasil disimpan');
    }

    /**
     * Update Roster Entry
     */
    public function updateRoster(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'unit' => 'required|string',
        ]);

        $r = RosterData::findOrFail($id);
        $r->update([
            'unit' => strtoupper(trim($request->unit)),
            'csa_type' => $request->csa_type ?? $r->csa_type,
        ]);

        return back()->with('success', 'Roster berhasil diperbarui');
    }

    /**
     * Delete Roster Entry
     */
    public function deleteRoster($id): RedirectResponse
    {
        RosterData::destroy($id);
        return back()->with('success', 'Data roster berhasil dihapus');
    }

    /**
     * Clear Roster for Date & Shift
     */
    public function clearRoster(Request $request): RedirectResponse
    {
        $date = $request->input('date', date('Y-m-d'));
        $shift = $request->input('shift', 'D');

        RosterData::where('tanggal', $date)->where('kode', $shift)->delete();

        return back()->with('success', 'Roster untuk tanggal ' . $date . ' shift ' . $shift . ' berhasil dibersihkan');
    }

    /**
     * Save / Sync Manpower Google Sheet URLs
     */
    public function syncGsheetManpower(Request $request): RedirectResponse
    {
        $urlDay = $request->input('url_day', '');
        $urlNight = $request->input('url_night', '');
        $date = $request->input('date', date('Y-m-d'));

        if ($urlDay) {
            Setting::setValue('google_sheet_manpower_url_day', $urlDay);
            Setting::setValue('google_sheet_manpower_url_day_' . $date, $urlDay);
        }
        if ($urlNight) {
            Setting::setValue('google_sheet_manpower_url_night', $urlNight);
            Setting::setValue('google_sheet_manpower_url_night_' . $date, $urlNight);
        }

        return back()->with('success', 'Konfigurasi Google Sheet Manpower berhasil disimpan');
    }

    /**
     * Master Unit Fleet: Store / Update
     */
    public function storeUnitFleet(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string',
            'category_type' => 'required|string',
        ]);

        if ($request->id) {
            $unit = Unit::findOrFail($request->id);
            $unit->update([
                'name' => $request->name,
                'category_type' => $request->category_type,
                'jumlah_unit' => (int) $request->input('jumlah_unit', 0),
            ]);
        } else {
            Unit::create([
                'name' => $request->name,
                'category_type' => $request->category_type,
                'jumlah_unit' => (int) $request->input('jumlah_unit', 0),
            ]);
        }

        return back()->with('success', 'Master unit fleet berhasil disimpan');
    }

    /**
     * Master Unit Fleet: Delete
     */
    public function deleteUnitFleet($id): RedirectResponse
    {
        Unit::destroy($id);
        return back()->with('success', 'Master unit fleet berhasil dihapus');
    }

    /**
     * Import Fleet Data API / Form
     */
    public function importFleet(Request $request): RedirectResponse
    {
        $urlDay = $request->input('url_day', '');
        $urlNight = $request->input('url_night', '');
        $date = $request->input('date', date('Y-m-d'));

        if ($urlDay) Setting::setValue('fleet_spreadsheet_url_day', $urlDay);
        if ($urlNight) Setting::setValue('fleet_spreadsheet_url_night', $urlNight);

        return back()->with('success', 'Import alokasi fleet berhasil diproses');
    }

    /**
     * Delete Fleet Allocation Data
     */
    public function deleteFleetData(Request $request): RedirectResponse
    {
        $date = $request->input('date', date('Y-m-d'));
        $shift = (int) $request->input('shift', 1);

        FleetAllocation::where('tanggal', $date)->where('shift', $shift)->delete();

        return back()->with('success', 'Data alokasi fleet berhasil dihapus');
    }

    /**
     * Monitoring Setting Manpower
     */
    public function monitoringManpower(Request $request): Response
    {
        $date = $request->query('date', date('Y-m-d'));
        $shift = $request->query('shift', 'D');

        $rosterData = RosterData::where('tanggal', $date)
            ->where('kode', $shift)
            ->orderBy('unit', 'asc')
            ->get()
            ->map(function ($r) {
                $emp = Employee::where('nrp', $r->nrp)->first();
                return [
                    'id' => $r->id,
                    'nrp' => $r->nrp,
                    'unit' => $r->unit,
                    'csa_type' => $r->csa_type ?: 'CSA GENERAL',
                    'employee_name' => $emp ? $emp->name : 'NRP ' . $r->nrp,
                    'phone' => $emp ? $emp->no_telepon : '-',
                    'jabatan' => $emp ? $emp->jabatan : 'Operator',
                    'departemen' => $emp ? $emp->departemen : 'PRODUKSI',
                ];
            });

        // Group by Unit
        $groupedByUnit = $rosterData->groupBy('unit');

        return Inertia::render('OneSetting/MonitoringManpower', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_setting_menu'),
            'filters' => ['date' => $date, 'shift' => $shift],
            'totalRoster' => $rosterData->count(),
            'groupedByUnit' => $groupedByUnit,
            'rosterData' => $rosterData,
        ]);
    }

    /**
     * Monitoring Setting Fleet
     */
    public function monitoringFleet(Request $request): Response
    {
        $date = $request->query('date', date('Y-m-d'));
        $shift = (int) $request->query('shift', 1);

        $allocations = FleetAllocation::where('tanggal', $date)
            ->where('shift', $shift)
            ->get();

        // Group allocations by group_name -> loader_id
        $grouped = [];
        foreach ($allocations as $item) {
            $gName = $item->group_name ?: 'UNGROUPED';
            $lId = $item->loader_id ?: 'UNASSIGNED';

            if (!isset($grouped[$gName])) {
                $grouped[$gName] = [];
            }
            if (!isset($grouped[$gName][$lId])) {
                $grouped[$gName][$lId] = [
                    'loader_id' => $lId,
                    'area' => $item->area,
                    'refueling' => $item->refueling_time,
                    'front' => $item->front,
                    'units' => [],
                ];
            }

            $grouped[$gName][$lId]['units'][] = [
                'id' => $item->id,
                'unit_nrp' => $item->unit_nrp,
                'unit_status' => $item->unit_status,
                'distance' => $item->distance,
            ];
        }

        // Summary metrics
        $totalUnits = $allocations->count();
        $rfuCount = $allocations->whereIn('unit_status', ['M', 'N', 'RFU'])->count();
        $breakdownCount = $allocations->whereIn('unit_status', ['G', 'J', 'R', 'BREAKDOWN'])->count();

        return Inertia::render('OneSetting/MonitoringFleet', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_setting_menu'),
            'filters' => ['date' => $date, 'shift' => $shift],
            'stats' => [
                'totalUnits' => $totalUnits,
                'rfuCount' => $rfuCount,
                'breakdownCount' => $breakdownCount,
            ],
            'groupedFleet' => $grouped,
        ]);
    }
}
