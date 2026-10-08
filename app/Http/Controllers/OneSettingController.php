<?php

namespace App\Http\Controllers;

use App\Http\Resources\FleetBoardResource;
use App\Http\Resources\ManpowerV201ViewResource;
use App\Models\Employee;
use App\Models\FleetAllocation;
use App\Models\RosterData;
use App\Models\Setting;
use App\Models\SettingBoard;
use App\Models\Unit;
use App\Services\FleetBoardService;
use App\Services\ManpowerV201SettingService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $historyMonth = $request->query('history_month', date('Y-m'));

        $totalManpowerRoster = RosterData::where('tanggal', $date)->count();
        $totalFleetUnits = Unit::count();
        $totalFleetAllocations = FleetAllocation::where('tanggal', $date)->count();
        $totalEmployees = Employee::count();

        [$historyYear, $historyMonthNum] = array_pad(explode('-', $historyMonth), 2, null);

        // Riwayat Import Fleet & Manpower: "batch" import disimpulkan dari (tanggal, shift)
        // yang sama (padanan v1 settings/fleet.php — tidak ada tabel log import terpisah).
        // Dibatasi per bulan (default bulan berjalan) supaya tabelnya tidak menumpuk semua histori.
        $fleetImportHistory = FleetAllocation::selectRaw('tanggal, shift, COUNT(*) as jumlah_unit, MAX(created_at) as last_import')
            ->whereYear('tanggal', $historyYear)
            ->whereMonth('tanggal', $historyMonthNum)
            ->groupBy('tanggal', 'shift')
            ->orderByDesc('tanggal')
            ->orderBy('shift')
            ->get();

        $manpowerImportHistory = RosterData::selectRaw('tanggal, kode as shift, COUNT(*) as jumlah_unit, MAX(created_at) as last_import')
            ->whereYear('tanggal', $historyYear)
            ->whereMonth('tanggal', $historyMonthNum)
            ->groupBy('tanggal', 'kode')
            ->orderByDesc('tanggal')
            ->orderBy('kode')
            ->get();

        return Inertia::render('OneSetting/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_setting_menu'),
            'filters' => ['date' => $date, 'shift' => $shift],
            'historyMonth' => $historyMonth,
            'stats' => [
                'totalManpowerRoster' => $totalManpowerRoster,
                'totalFleetUnits' => $totalFleetUnits,
                'totalFleetAllocations' => $totalFleetAllocations,
                'totalEmployees' => $totalEmployees,
            ],
            'fleetImportHistory' => $fleetImportHistory,
            'manpowerImportHistory' => $manpowerImportHistory,
        ]);
    }

    /**
     * Halaman Setting Board: papan gambar HD (Manpower & Fleet) per site/tanggal/shift.
     */
    public function settingBoard(Request $request, ManpowerV201SettingService $manpowerSetting, FleetBoardService $fleetBoard): Response
    {
        $site = strtoupper($request->query('site', 'ADW'));
        $date = $request->query('date', date('Y-m-d'));
        $shift = $request->query('shift', 'D');

        $boards = SettingBoard::where('site', $site)
            ->where('tanggal', $date)
            ->where('shift', $shift)
            ->get();

        $settingBoards = [
            'manpower' => null,
            'fleet' => null,
        ];

        foreach ($boards as $b) {
            $settingBoards[$b->type] = [
                'id' => $b->id,
                'site' => $b->site,
                'tanggal' => $b->tanggal instanceof \DateTimeInterface ? $b->tanggal->format('Y-m-d') : (string) $b->tanggal,
                'shift' => $b->shift,
                'type' => $b->type,
                'title' => $b->title,
                'notes' => $b->notes,
                'image_url' => asset('storage/'.$b->image_path),
                'created_at' => $b->created_at?->format('Y-m-d H:i:s'),
            ];
        }

        return Inertia::render('OneSetting/SettingBoard', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_setting_menu'),
            'filters' => ['site' => $site, 'date' => $date, 'shift' => $shift],
            'settingBoards' => $settingBoards,
            'manpowerPreview' => $this->manpowerPreview($manpowerSetting, $date, $shift),
            'fleetPreview' => $this->fleetPreview($fleetBoard, $date, $shift),
        ]);
    }

    /** Papan Fleet = lembar dari fleet_allocations (tanggal + shift yang sama). Null bila belum ada data. */
    private function fleetPreview(FleetBoardService $board, string $date, string $shift): ?array
    {
        try {
            $sheet = $board->sheet($date, $shift);
        } catch (\Throwable) {
            return null;
        }

        return $sheet ? ['sheet' => FleetBoardResource::make($sheet)->resolve()] : null;
    }

    /**
     * Papan Manpower = preview Setting Manpower (tanggal + shift yang sama). Null bila tabel belum ada,
     * tanggal/shift tidak valid, atau setting belum dibuat.
     */
    private function manpowerPreview(ManpowerV201SettingService $setting, string $date, string $shift): ?array
    {
        try {
            $view = $setting->view($date, $shift);
        } catch (\Throwable) {
            return null;
        }

        if (! $view['setting']) {
            return null;
        }

        return [
            'view' => ManpowerV201ViewResource::make($view)->resolve(),
            'checkPointPassword' => $setting->checkPointPassword($date, $shift),
        ];
    }

    /**
     * Upload Image Setting Board (HD)
     */
    public function uploadSettingBoard(Request $request): RedirectResponse
    {
        $request->validate([
            'site' => 'required|string',
            'tanggal' => 'required|date',
            'shift' => 'required|string',
            'type' => 'required|in:manpower,fleet',
            'image' => 'required|file|mimes:jpeg,png,jpg,webp,svg|max:25600',
            'title' => 'nullable|string|max:255',
        ]);

        $file = $request->file('image');
        $site = strtoupper(trim($request->site));
        $ext = $file->getClientOriginalExtension();
        $filename = $request->type.'_'.$site.'_'.$request->tanggal.'_'.$request->shift.'_'.time().'.'.$ext;
        $path = $file->storeAs('setting_boards', $filename, 'public');

        // Check and remove previous image if replacing
        $existing = SettingBoard::where('site', $site)
            ->where('tanggal', $request->tanggal)
            ->where('shift', $request->shift)
            ->where('type', $request->type)
            ->first();

        if ($existing && $existing->image_path && Storage::disk('public')->exists($existing->image_path)) {
            Storage::disk('public')->delete($existing->image_path);
        }

        SettingBoard::updateOrCreate(
            [
                'site' => $site,
                'tanggal' => $request->tanggal,
                'shift' => $request->shift,
                'type' => $request->type,
            ],
            [
                'image_path' => $path,
                'title' => $request->title ?: ($request->type === 'manpower' ? 'Setting Manpower Departement Produksi' : 'Setting Fleet Dept Produksi'),
                'created_by' => auth()->id(),
            ]
        );

        return back()->with('success', 'Papan setting gambar berhasil disimpan.');
    }

    /**
     * Delete Image Setting Board
     */
    public function deleteSettingBoard($id): RedirectResponse
    {
        $board = SettingBoard::findOrFail($id);
        if ($board->image_path && Storage::disk('public')->exists($board->image_path)) {
            Storage::disk('public')->delete($board->image_path);
        }
        $board->delete();

        return back()->with('success', 'Papan setting gambar berhasil dihapus.');
    }

    /**
     * Save Snapshot Board from base64 image (html2canvas result)
     * Called from MonitoringManpower / MonitoringFleet pages after import.
     */
    public function saveSnapshotBoard(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'site'      => 'required|string',
            'tanggal'   => 'required|date',
            'shift'     => 'required|string',
            'type'      => 'required|in:manpower,fleet',
            'image_b64' => 'required|string', // base64 data URL: data:image/png;base64,...
            'title'     => 'nullable|string|max:255',
        ]);

        // Decode base64
        $b64Data = $request->input('image_b64');
        // Detect format from data URL header
        $ext = 'jpg';
        if (str_contains($b64Data, 'data:image/png')) $ext = 'png';
        // Strip data URL header if present
        if (str_contains($b64Data, ',')) {
            $b64Data = substr($b64Data, strpos($b64Data, ',') + 1);
        }
        $imageData = base64_decode($b64Data);
        if (!$imageData || strlen($imageData) < 500) {
            return response()->json(['success' => false, 'message' => 'Gambar tidak valid atau terlalu kecil.'], 422);
        }

        $site = strtoupper(trim($request->site));
        $filename = $request->type . '_' . $site . '_' . $request->tanggal . '_' . $request->shift . '_snapshot_' . time() . '.' . $ext;
        $path = 'setting_boards/' . $filename;

        // Remove old image if exists
        $existing = SettingBoard::where('site', $site)
            ->where('tanggal', $request->tanggal)
            ->where('shift', $request->shift)
            ->where('type', $request->type)
            ->first();

        if ($existing && $existing->image_path && Storage::disk('public')->exists($existing->image_path)) {
            Storage::disk('public')->delete($existing->image_path);
        }

        Storage::disk('public')->put($path, $imageData);

        $board = SettingBoard::updateOrCreate(
            [
                'site'    => $site,
                'tanggal' => $request->tanggal,
                'shift'   => $request->shift,
                'type'    => $request->type,
            ],
            [
                'image_path'  => $path,
                'title'       => $request->title ?: ($request->type === 'manpower'
                    ? 'Setting Manpower Departement Produksi'
                    : 'Setting Fleet Dept Produksi'),
                'created_by'  => auth()->id(),
            ]
        );

        return response()->json([
            'success'   => true,
            'message'   => 'Papan setting berhasil dijadikan gambar dan disimpan.',
            'image_url' => asset('storage/' . $path),
            'board_id'  => $board->id,
        ]);
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
     * Clear Roster for Date & Shift (dipakai tombol "Reset Data" di Monitoring Alokasi Manpower)
     */
    public function clearRoster(Request $request): RedirectResponse
    {
        $date = $request->input('date', date('Y-m-d'));
        $shift = $request->input('shift', 'D');

        RosterData::where('tanggal', $date)->where('kode', $shift)->delete();

        return back()->with('success', 'Roster untuk tanggal '.$date.' shift '.$shift.' berhasil dibersihkan');
    }

    /**
     * Store Location Lock
     */
    public function storeLocationLock(Request $request): RedirectResponse
    {
        $request->validate([
            'location' => 'required|string',
            'units' => 'required|array',
        ]);

        $locks = json_decode(Setting::getValue('unit_location_locks', '[]'), true) ?: [];
        
        $newLock = [
            'id' => uniqid(),
            'location' => strtoupper(trim($request->location)),
            'units' => array_map(fn($u) => strtoupper(trim($u)), $request->units),
        ];

        $locks[] = $newLock;
        Setting::setValue('unit_location_locks', json_encode($locks));

        return back()->with('success', 'Kunci lokasi unit berhasil ditambahkan');
    }

    /**
     * Delete Location Lock
     */
    public function deleteLocationLock($id): RedirectResponse
    {
        $locks = json_decode(Setting::getValue('unit_location_locks', '[]'), true) ?: [];
        $locks = array_filter($locks, fn($l) => $l['id'] !== $id);

        // Re-index array
        Setting::setValue('unit_location_locks', json_encode(array_values($locks)));

        return back()->with('success', 'Kunci lokasi unit berhasil dihapus');
    }

    /**
     * Store Category Lock (mapping kode unit -> kategori unit, mis. HAUL TRUCK, EXCAVATOR SUPPORT).
     * Dipakai papan Setting Manpower supaya kategori bisa dikoreksi admin, bukan cuma tebakan otomatis.
     */
    public function storeCategoryLock(Request $request): RedirectResponse
    {
        $request->validate([
            'category' => 'required|string',
            'units' => 'required|array',
        ]);

        $locks = json_decode(Setting::getValue('unit_category_locks', '[]'), true) ?: [];

        $locks[] = [
            'id' => uniqid(),
            'category' => strtoupper(trim($request->category)),
            'units' => array_map(fn ($u) => strtoupper(trim($u)), $request->units),
        ];

        Setting::setValue('unit_category_locks', json_encode($locks));

        return back()->with('success', 'Kunci kategori unit berhasil ditambahkan');
    }

    /**
     * Delete Category Lock
     */
    public function deleteCategoryLock($id): RedirectResponse
    {
        $locks = json_decode(Setting::getValue('unit_category_locks', '[]'), true) ?: [];
        $locks = array_filter($locks, fn ($l) => $l['id'] !== $id);

        Setting::setValue('unit_category_locks', json_encode(array_values($locks)));

        return back()->with('success', 'Kunci kategori unit berhasil dihapus');
    }

    private function importManpowerCsv(string $url, string $date, string $shift): void
    {
        // Load Location Locks
        $locationLocksStr = Setting::getValue('unit_location_locks', '[]');
        $locationLocks = json_decode($locationLocksStr, true) ?: [];
        
        // Convert google sheet link to CSV export link
        if (preg_match('/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
            $spreadsheetId = $matches[1];
            $gid = '0';
            if (preg_match('/gid=([0-9]+)/', $url, $gidMatches)) {
                $gid = $gidMatches[1];
            }
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv&gid={$gid}";

            try {
                $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 15]]);
                $csvData = @file_get_contents($csvUrl, false, $context);
                
                if (!$csvData) return;

                $lines = explode("\n", $csvData);
                if (count($lines) < 2) return;

                // Find ALL NRP, UNIT, and NAMA columns (for side-by-side tables)
                $unitCols = [];
                $nrpCols = [];
                $nameCols = [];
                $headerFound = false;
                
                // Scan first 10 rows to find headers
                for ($i = 0; $i < min(10, count($lines)); $i++) {
                    $row = str_getcsv($lines[$i]);
                    $foundNrpInThisRow = [];
                    $foundUnitInThisRow = [];
                    $foundNameInThisRow = [];
                    foreach ($row as $idx => $cell) {
                        $label = strtoupper(trim($cell));
                        if ($label === 'NRP' || str_contains($label, 'NRP')) $foundNrpInThisRow[] = $idx;
                        if ($label === 'UNIT' || str_contains($label, 'NO UNIT')) $foundUnitInThisRow[] = $idx;
                        if ($label === 'NAMA' || str_contains($label, 'NAMA')) $foundNameInThisRow[] = $idx;
                    }
                    if (count($foundNrpInThisRow) > 0 && count($foundUnitInThisRow) > 0) {
                        $nrpCols = $foundNrpInThisRow;
                        $unitCols = $foundUnitInThisRow;
                        $nameCols = $foundNameInThisRow;
                        $lines = array_slice($lines, $i + 1);
                        $headerFound = true;
                        break;
                    }
                }

                // Pair them up. Assume each NRP corresponds to the closest Unit column to its left, and Name column to its right
                $pairs = [];
                if ($headerFound) {
                    $usedUnitIdx = [];
                    foreach ($nrpCols as $nrpIdx) {
                        $bestUnitIdx = -1;
                        foreach ($unitCols as $unitIdx) {
                            if ($unitIdx < $nrpIdx && $unitIdx > $bestUnitIdx && !in_array($unitIdx, $usedUnitIdx)) {
                                $bestUnitIdx = $unitIdx;
                            }
                        }
                        
                        $bestNameIdx = -1;
                        foreach ($nameCols as $nameIdx) {
                            if ($nameIdx > $nrpIdx) {
                                $bestNameIdx = $nameIdx;
                                break; // Take the first Name column to the right
                            }
                        }

                        if ($bestUnitIdx !== -1) {
                            $usedUnitIdx[] = $bestUnitIdx;
                            $pairs[] = [
                                'unit' => $bestUnitIdx, 
                                'nrp' => $nrpIdx,
                                'name' => $bestNameIdx !== -1 ? $bestNameIdx : $nrpIdx + 1 // fallback to nrp+1 if no name col found
                            ];
                        }
                    }
                } else {
                    // Fallback to assuming Unit is col 0, NRP is col 2, Name is col 3
                    $pairs[] = ['unit' => 0, 'nrp' => 2, 'name' => 3];
                }

                // Keywords to exclude (not real units)
                $excludeKw = ['SPARE', '-', 'OFF', 'STANDBY', 'ALL DISPOSAL', 'ALL FRONT', 'COAL', 'DISPOSAL', 'FRONT', 'HRM', 'MANPOWER', 'PSV', 'MCU', 'TR', 'ID', 'IDS', 'CUTI', 'SAKIT', 'ALPA', 'MANGKIR', 'IZIN', 'SICK', 'LEAVE', 'TRAINING', 'REMARK'];

                $upsertData = [];
                $uniqueUnits = []; // Map to track unit indexes to FORCE overwrite and prevent clashes
                
                foreach ($lines as $line) {
                    $row = str_getcsv($line);
                    foreach ($pairs as $pair) {
                        $unitIdx = $pair['unit'];
                        $nrpIdx = $pair['nrp'];
                        $nameIdx = $pair['name'];

                        if (isset($row[$unitIdx]) && isset($row[$nrpIdx])) {
                            $unit = trim($row[$unitIdx]);
                            $nrp = trim($row[$nrpIdx]);
                            $name = isset($row[$nameIdx]) ? trim($row[$nameIdx]) : '';

                            // Clean NRP just in case
                            $nrpClean = preg_replace('/[^0-9]/', '', $nrp);

                            // Validasi NRP (angka), Unit (tidak kosong), dan Nama (tidak kosong)
                            if ($nrpClean !== '' && strlen($nrpClean) >= 4 && $unit !== '' && $name !== '') {
                                $unitUpper = strtoupper($unit);
                                $csaType = 'CSA GENERAL';
                                
                                foreach ($locationLocks as $lock) {
                                    if (in_array($unitUpper, $lock['units'])) {
                                        $csaType = $lock['location'];
                                        break;
                                    }
                                }

                                $isExcluded = false;
                                foreach ($excludeKw as $kw) {
                                    if (str_contains($unitUpper, $kw)) {
                                        $isExcluded = true;
                                        break;
                                    }
                                }

                                $upsertData[$nrpClean] = [
                                    'tanggal' => $date,
                                    'kode' => $shift,
                                    'nrp' => $nrpClean,
                                    'unit' => $unitUpper,
                                    'csa_type' => $csaType,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }
                }
                

                // Konversi kembali ke array numerik setelah deduplikasi berdasarkan NRP
                $upsertData = array_values($upsertData);

                if (empty($upsertData)) {
                    // Check if it looks like HTML (meaning it's private or requires login)
                    if (str_contains(substr($csvData, 0, 100), '<html') || str_contains(substr($csvData, 0, 100), '<!DOCTYPE')) {
                        throw new \Exception('Gagal: Google Sheet bersifat Private. Pastikan akses Sheet diatur ke "Anyone with the link can view".');
                    }
                    throw new \Exception('Gagal: Format tidak dikenali atau tidak ada data. Pastikan ada judul kolom "NRP" dan "UNIT".');
                }

                \Illuminate\Support\Facades\DB::transaction(function () use ($upsertData, $date, $shift) {
                    RosterData::where('tanggal', $date)->where('kode', $shift)->delete();
                    RosterData::insert($upsertData);
                });
            } catch (\Exception $e) {
                // We re-throw it so the outer function can catch it and show to user
                throw $e;
            }
        } else {
            throw new \Exception('Gagal: Link Google Sheet tidak valid.');
        }
    }

    public function syncGsheetManpower(Request $request): RedirectResponse
    {
        $urlDay = $request->input('url_day', '');
        $urlNight = $request->input('url_night', '');
        $date = $request->input('date', date('Y-m-d'));

        try {
            if ($urlDay) {
                Setting::setValue('google_sheet_manpower_url_day', $urlDay);
                Setting::setValue('google_sheet_manpower_url_day_' . $date, $urlDay);
                $this->importManpowerCsv($urlDay, $date, 'D');
            }
            if ($urlNight) {
                Setting::setValue('google_sheet_manpower_url_night', $urlNight);
                Setting::setValue('google_sheet_manpower_url_night_' . $date, $urlNight);
                $this->importManpowerCsv($urlNight, $date, 'N');
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Sinkronisasi Google Sheet Manpower berhasil diproses.');
    }

    /**
     * Import Fleet Data API / Form
     */
    public function importFleet(Request $request): RedirectResponse
    {
        $urlDay = $request->input('url_day', '');
        $urlNight = $request->input('url_night', '');
        $date = $request->input('date', date('Y-m-d'));

        try {
            if ($urlDay) {
                Setting::setValue('fleet_spreadsheet_url_day', $urlDay);
                Setting::setValue('fleet_spreadsheet_url_day_' . $date, $urlDay);
                $this->importFleetCsv($urlDay, $date, 1);
            }
            if ($urlNight) {
                Setting::setValue('fleet_spreadsheet_url_night', $urlNight);
                Setting::setValue('fleet_spreadsheet_url_night_' . $date, $urlNight);
                $this->importFleetCsv($urlNight, $date, 2);
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Import alokasi fleet berhasil diproses');
    }

    private function googleSheetUrlToCsv(string $url): ?string
    {
        $id = null;
        $gid = '0';
        if (preg_match('#/d/([a-zA-Z0-9-_]+)#', $url, $m)) {
            $id = $m[1];
        }
        if (preg_match('/[\?#&]gid=(\d+)/', $url, $m)) {
            $gid = $m[1];
        }
        if (!$id) return null;
        return "https://docs.google.com/spreadsheets/d/$id/export?format=csv&gid=$gid";
    }

    private function importFleetCsv(string $url, string $date, int $shift): void
    {
        $csvUrl = $this->googleSheetUrlToCsv($url);
        if (!$csvUrl) {
            throw new \Exception('URL Spreadsheet tidak valid atau tidak publik.');
        }

        $csvData = @file_get_contents($csvUrl);
        if (!$csvData) {
            throw new \Exception('Gagal mengunduh data CSV Fleet. Pastikan link Google Sheet bersifat publik (Anyone with the link can view).');
        }

        $lines = str_getcsv($csvData, "\n");
        $rows = array_map(function($l) { return str_getcsv($l, ","); }, $lines);

        $allocations = [];
        $columnGroups = [];
        $groupHeaderRow = -1;
        $currentGroup = 'LAINNYA';

        foreach ($rows as $rIdx => $row) {
            // Skip rows that look like Summary/Notes tables
            $rowText = implode(' ', $row);
            if (preg_match('/KETERANGAN|POSISI AKTUAL|NOTE:|CATATAN/i', $rowText)) continue;

            foreach ($row as $cIdx => $cell) {
                $cell = trim($cell);
                if (empty($cell)) continue;
                
                // Skip cells that are headers for the summary table
                if (in_array(strtoupper($cell), ['KETERANGAN', 'POSISI AKTUAL', 'NOTE', 'UNIT'])) {
                    if (isset($row[$cIdx+1]) && strtoupper(trim($row[$cIdx+1])) === 'KETERANGAN') break;
                }

                // Detect Group Header
                $matchedGroup = null;
                if (preg_match('/(BARAT|TIMUR|CENTRAL|NORTH|SOUTH|EWT|IPPKH|SOIL|COAL|OB|GETTING|BREAKDOWN|ALL AREA)/i', $cell)) {
                    $rowNonEmpty = count(array_filter($row, function($v) { return !empty(trim($v)); }));
                    if ($rowNonEmpty <= 5) {
                        $matchedGroup = $cell;
                    }
                }

                if ($matchedGroup) {
                    $nonEmptyCount = count(array_filter($row, function($v) { return !empty(trim($v)); }));
                    if ($nonEmptyCount <= 4 && $groupHeaderRow !== $rIdx) {
                        $columnGroups = [];
                    }
                    $groupHeaderRow = $rIdx;
                    $currentGroup = $matchedGroup;
                    $columnGroups[$cIdx] = $matchedGroup;
                }

                // Detect Loader ID
                $isSpecial = preg_match('/ALL FLEET|NO OPERATOR|RFU WS|PLAN SERVICE|BREAKDOWN|SERVICE \& BD/i', $cell);
                if (preg_match('/^E\d{3,5}$|^EX\d{2,3}$/i', $cell) || $isSpecial) {
                    $loaderId = strtoupper($cell);
                    
                    $maxIdx = -1;
                    $loaderGroup = 'LAINNYA';
                    foreach ($columnGroups as $gColIdx => $gName) {
                        if ($gColIdx <= $cIdx && $gColIdx > $maxIdx) {
                            $maxIdx = $gColIdx;
                            $loaderGroup = $gName;
                        }
                    }
                    if ($loaderGroup === 'LAINNYA') $loaderGroup = $currentGroup;
                    if ($isSpecial && $loaderGroup === 'LAINNYA') {
                        $loaderGroup = 'STATUS / SUMMARY';
                    }

                    $refueling = '';
                    $area = '';
                    $foundCount = 0;
                    for ($up = 1; $up <= 10; $up++) {
                        $uRIdx = $rIdx - $up;
                        if ($uRIdx < 0) break;
                        
                        $valAbove = '';
                        // Handle merged cells in CSV by scanning leftwards up to 5 columns
                        for ($left = 0; $left <= 5; $left++) {
                            $checkCol = $cIdx - $left;
                            if ($checkCol < 0) break;
                            $val = trim($rows[$uRIdx][$checkCol] ?? '');
                            if (!empty($val)) {
                                $valAbove = $val;
                                break;
                            }
                        }

                        if (!empty($valAbove)) {
                            $foundCount++;
                            if ($foundCount === 1) {
                                $refueling = $valAbove;
                            } elseif ($foundCount === 2) {
                                $area = $valAbove;
                                break;
                            }
                        }
                    }
                    
                    $units = [];
                    $lastUnitRow = $rIdx;
                    
                    for ($i = 1; $i < 20; $i++) {
                        $uRowIdx = $rIdx + $i;
                        if (!isset($rows[$uRowIdx])) break;
                        
                        $unitCell = trim($rows[$uRowIdx][$cIdx] ?? '');
                        if (empty($unitCell)) continue;
                        
                        if (preg_match('/RELOKASI|FRONT|HAULER|UNIT/i', $unitCell)) break;
                        if (strlen($unitCell) > 15) break; 

                        if (preg_match('/^([A-Z]+\d+[A-Z]*|\d+[A-Z]+)\s*([A-Z])?$/i', $unitCell, $m)) {
                            $unitNrp = $m[1];
                            $status = $m[2] ?? (trim($rows[$uRowIdx][$cIdx+1] ?? ''));
                            $units[] = [
                                'nrp' => strtoupper($unitNrp), 
                                'status' => strtoupper(substr(trim($status), 0, 1))
                            ];
                            $lastUnitRow = $uRowIdx;
                        }
                    }

                    $distance = 0;
                    $front = '';
                    
                    if (!$isSpecial) {
                        for ($j = 1; $j < 50; $j++) {
                            $currRowIdx = $lastUnitRow + $j;
                            if (!isset($rows[$currRowIdx])) break;
                            
                            for ($offset = -1; $offset <= 1; $offset++) {
                                $targetCol = $cIdx + $offset;
                                if (!isset($rows[$currRowIdx][$targetCol])) continue;
                                
                                $val = trim($rows[$currRowIdx][$targetCol]);
                                if (empty($val)) continue;

                                if ($distance === 0 && preg_match('/^\d{3,5}$/', $val)) {
                                    $distance = (int)$val;
                                } 
                                elseif (empty($front) && strlen($val) >= 6 && !preg_match('/^\d+$/', $val)) {
                                    if (!preg_match('/(SHIF|TANGGAL|NOTE|KET|TOTAL|UNIT|DISTANCE|JARAK|HAULER|RELOKASI)/i', $val) && $val !== $loaderId) {
                                        $front = $val;
                                    }
                                }
                            }
                            if (!empty($front) && $distance > 0) {
                                if ($j > 10) break;
                            }
                        }
                    }

                    if (empty($front) && $loaderGroup !== 'LAINNYA' && $loaderGroup !== 'STATUS / SUMMARY' && !$isSpecial) {
                        $front = $loaderGroup;
                    }

                    foreach ($units as $u) {
                        $allocations[] = [
                            'tanggal' => $date,
                            'shift' => $shift,
                            'group_name' => $loaderGroup,
                            'loader_id' => $loaderId,
                            'refueling_time' => $refueling,
                            'area' => $area,
                            'unit_nrp' => $u['nrp'],
                            'unit_status' => $u['status'],
                            'front' => $front,
                            'distance' => $distance,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }

        if (empty($allocations)) {
            throw new \Exception('Shift ' . $shift . ': Tidak ada data fleet yang terdeteksi. Pastikan format spreadsheet sesuai.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($allocations, $date, $shift) {
            FleetAllocation::where('tanggal', $date)->where('shift', $shift)->delete();
            FleetAllocation::insert($allocations);
        });
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

        // GSheet Manpower settings
        $gsheetManpowerDay = Setting::getValue('google_sheet_manpower_url_day_' . $date) 
            ?: Setting::getValue('google_sheet_manpower_url_day', '');
        $gsheetManpowerNight = Setting::getValue('google_sheet_manpower_url_night_' . $date) 
            ?: Setting::getValue('google_sheet_manpower_url_night', '');

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
                    'jabatan' => $emp ? ($emp->jabatan_roster ?: ($emp->jabatan_ss6 ?: 'Operator')) : 'Operator',
                    'departemen' => $emp ? ($emp->lokasi_kerja ?: 'PRODUKSI') : 'PRODUKSI',
                ];
            });

        // Group by Unit
        $groupedByUnit = $rosterData->groupBy('unit');

        $locationLocks = json_decode(Setting::getValue('unit_location_locks', '[]'), true) ?: [];
        $categoryLocks = json_decode(Setting::getValue('unit_category_locks', '[]'), true) ?: [];

        // "PASSWORD CHECK POINT" di papan snapshot ditarik dari fitur Password CP yang sama (pw_speakup_cp).
        $this->ensureLegacyTablesExist();
        $pwCpShift = $shift === 'N' ? '2' : '1';
        $passwordCheckPoint = DB::table('pw_speakup_cp')
            ->where('tanggal', $date)
            ->where('shift', $pwCpShift)
            ->value('password');

        return Inertia::render('OneSetting/MonitoringManpower', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_setting_menu'),
            'filters' => ['date' => $date, 'shift' => $shift],
            'gsheetManpower' => [
                'day' => $gsheetManpowerDay,
                'night' => $gsheetManpowerNight,
            ],
            'totalRoster' => $rosterData->count(),
            'groupedByUnit' => $groupedByUnit,
            'rosterData' => $rosterData,
            'locationLocks' => $locationLocks,
            'categoryLocks' => $categoryLocks,
            'passwordCheckPoint' => $passwordCheckPoint,
        ]);
    }

    /**
     * Monitoring Setting Fleet
     */
    public function monitoringFleet(Request $request): Response
    {
        $date = $request->query('date', date('Y-m-d'));
        $shift = (int) $request->query('shift', 1);

        // GSheet Fleet settings
        $gsheetFleetDay = Setting::getValue('fleet_spreadsheet_url_day_' . $date) 
            ?: Setting::getValue('fleet_spreadsheet_url_day', '');
        $gsheetFleetNight = Setting::getValue('fleet_spreadsheet_url_night_' . $date) 
            ?: Setting::getValue('fleet_spreadsheet_url_night', '');

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
            'gsheetFleet' => [
                'day' => $gsheetFleetDay,
                'night' => $gsheetFleetNight,
            ],
            'stats' => [
                'totalUnits' => $totalUnits,
                'rfuCount' => $rfuCount,
                'breakdownCount' => $breakdownCount,
            ],
            'groupedFleet' => $grouped,
        ]);
    }

    // =========================================================================
    // LEGACY SETTINGS (V1) MIGRATED TO VUE
    // =========================================================================

    private function ensureLegacyTablesExist()
    {
        // 1. unit_support_lokasi
        DB::statement("CREATE TABLE IF NOT EXISTS unit_support_lokasi (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            tanggal    DATE NOT NULL,
            shift      VARCHAR(5)   NOT NULL DEFAULT '1',
            unit       VARCHAR(50)  NOT NULL,
            lokasi     VARCHAR(200) NOT NULL DEFAULT '',
            keterangan VARCHAR(255) NOT NULL DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_tgl_shift_unit (tanggal, shift, unit)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 2. posisi_dozer_pc
        DB::statement("CREATE TABLE IF NOT EXISTS posisi_dozer_pc (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            tanggal    DATE NOT NULL,
            shift      VARCHAR(5)   NOT NULL DEFAULT '1',
            kategori   VARCHAR(20)  NOT NULL DEFAULT '',
            unit       VARCHAR(50)  NOT NULL,
            unit_label VARCHAR(80)  NOT NULL DEFAULT '',
            posisi     VARCHAR(200) NOT NULL DEFAULT '',
            keterangan VARCHAR(150) NOT NULL DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_tgl_shift_unit_dozer (tanggal, shift, unit)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 3. pw_speakup_cp
        DB::statement("CREATE TABLE IF NOT EXISTS pw_speakup_cp (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            tanggal    DATE NOT NULL,
            shift      VARCHAR(5)   NOT NULL DEFAULT '1',
            password   VARCHAR(200) NOT NULL DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_tgl_shift_pw (tanggal, shift)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 4. icc_gl_settings
        DB::statement("CREATE TABLE IF NOT EXISTS icc_gl_settings (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            tanggal    DATE NOT NULL,
            shift      VARCHAR(5)   NOT NULL DEFAULT '1',
            unit       VARCHAR(50)  NOT NULL,
            front_pic  VARCHAR(100) NOT NULL DEFAULT '',
            disposal_pic VARCHAR(100) NOT NULL DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_tgl_shift_unit_gl (tanggal, shift, unit)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function legacyGl(Request $request)
    {
        $this->ensureLegacyTablesExist();
        $date = $request->query('date', date('Y-m-d'));
        $shift = $request->query('shift', '1');

        $rows = DB::table('icc_gl_settings')->where('tanggal', $date)->where('shift', $shift)->orderBy('unit', 'asc')->get();
        $history = DB::table('icc_gl_settings')->select('tanggal', 'shift')->distinct()->orderBy('tanggal', 'desc')->orderBy('shift', 'asc')->limit(30)->get();

        return Inertia::render('OneSetting/Legacy/SettingGl', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_setting_menu'),
            'filters' => ['date' => $date, 'shift' => $shift],
            'rows' => $rows,
            'history' => $history,
        ]);
    }

    public function storeLegacyGl(Request $request)
    {
        $date = $request->input('date');
        $shift = $request->input('shift');
        $frontText = $request->input('front_text', '');
        $disposalText = $request->input('disposal_text', '');
        
        $data = [];
        $stripWa = fn($line) => trim(trim(trim($line), '_*'));
        $unitShortMap = ['1202' => 'E51202', '3918' => 'E53918'];
        $normalizeUnit = function ($digits) use ($unitShortMap) {
            return $unitShortMap[$digits] ?? ('E' . $digits);
        };

        foreach (preg_split('/\r?\n/', $frontText) as $line) {
            $line = $stripWa($line);
            if (preg_match('/^E\s+(\d{3,6})\s{2,}(.+)$/u', $line, $m)) {
                $u = $normalizeUnit(trim($m[1]));
                if (!isset($data[$u])) $data[$u] = ['front_pic'=>'','disposal_pic'=>''];
                $data[$u]['front_pic'] = trim($m[2], "_* \t");
            }
        }

        $currentPic = '';
        foreach (preg_split('/\r?\n/', $disposalText) as $line) {
            $line = $stripWa($line);
            if (preg_match('/^Pic\s*:\s*(.+)$/ui', $line, $m)) {
                $currentPic = trim($m[1], "_* \t");
            }
            if ($currentPic && preg_match('/^[-–]\s*E\s*(\d{3,6})\s*$/u', $line, $m)) {
                $u = $normalizeUnit(trim($m[1]));
                if (!isset($data[$u])) $data[$u] = ['front_pic'=>'','disposal_pic'=>''];
                $data[$u]['disposal_pic'] = $currentPic;
            }
        }

        if (empty($data)) {
            return back()->with('error', 'Tidak ada data yang berhasil diparsing.');
        }

        DB::transaction(function() use ($data, $date, $shift) {
            DB::table('icc_gl_settings')->where('tanggal', $date)->where('shift', $shift)->delete();
            $insertData = [];
            foreach ($data as $u => $v) {
                $insertData[] = [
                    'tanggal' => $date,
                    'shift' => $shift,
                    'unit' => $u,
                    'front_pic' => $v['front_pic'],
                    'disposal_pic' => $v['disposal_pic']
                ];
            }
            DB::table('icc_gl_settings')->insert($insertData);
        });

        return back()->with('success', count($data) . ' unit berhasil disimpan.');
    }

    public function deleteLegacyGl($id)
    {
        DB::table('icc_gl_settings')->where('id', $id)->delete();
        return back()->with('success', 'Data berhasil dihapus.');
    }

    // --- UNIT SUPPORT ---

    public function legacyUnitSupport(Request $request)
    {
        $this->ensureLegacyTablesExist();
        $date = $request->query('date', date('Y-m-d'));
        $shift = $request->query('shift', '1');

        $rows = DB::table('unit_support_lokasi')->where('tanggal', $date)->where('shift', $shift)->orderBy('lokasi', 'asc')->orderBy('unit', 'asc')->get();
        $history = DB::table('unit_support_lokasi')->select('tanggal', 'shift')->distinct()->orderBy('tanggal', 'desc')->orderBy('shift', 'asc')->limit(30)->get();

        return Inertia::render('OneSetting/Legacy/SettingUnitSupport', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_setting_menu'),
            'filters' => ['date' => $date, 'shift' => $shift],
            'rows' => $rows,
            'history' => $history,
        ]);
    }

    public function storeLegacyUnitSupport(Request $request)
    {
        $date = $request->input('date');
        $shift = $request->input('shift');
        $raw = $request->input('raw_text', '');
        $data = [];

        $currentLokasi = '';
        foreach (preg_split('/\r?\n/', $raw) as $line) {
            $line = trim(trim(trim($line), '_*'));
            if ($line === '') continue;

            if (preg_match('/^[-–]\s*(.+)$/', $line, $m)) {
                $rest = trim($m[1]);
                if (preg_match('/^([A-Za-z]+\s*\d+[A-Za-z0-9]*)\s*(.*)$/', $rest, $u)) {
                    $unit = strtoupper(preg_replace('/\s+/', '', $u[1]));
                    $ket  = trim($u[2]);
                } else {
                    $parts = preg_split('/\s+/', $rest, 2);
                    $unit  = strtoupper($parts[0]);
                    $ket   = trim($parts[1] ?? '');
                }
                if ($unit && $currentLokasi) {
                    $data[$unit] = ['lokasi' => $currentLokasi, 'keterangan' => $ket];
                }
            } else {
                $currentLokasi = $line;
            }
        }

        if (empty($data)) return back()->with('error', 'Tidak ada data yang diparsing.');

        DB::transaction(function() use ($data, $date, $shift) {
            DB::table('unit_support_lokasi')->where('tanggal', $date)->where('shift', $shift)->delete();
            $ins = [];
            foreach ($data as $u => $v) {
                $ins[] = [
                    'tanggal' => $date, 'shift' => $shift, 'unit' => $u,
                    'lokasi' => $v['lokasi'], 'keterangan' => $v['keterangan']
                ];
            }
            DB::table('unit_support_lokasi')->insert($ins);
        });

        return back()->with('success', count($data) . ' unit disimpan.');
    }

    public function deleteLegacyUnitSupport($id)
    {
        DB::table('unit_support_lokasi')->where('id', $id)->delete();
        return back()->with('success', 'Data dihapus.');
    }

    // --- DOZER & PC ---

    public function legacyDozerPc(Request $request)
    {
        $this->ensureLegacyTablesExist();
        $date = $request->query('date', date('Y-m-d'));
        $shift = $request->query('shift', '1');

        $rows = DB::table('posisi_dozer_pc')->where('tanggal', $date)->where('shift', $shift)->orderBy('kategori', 'asc')->orderBy('unit', 'asc')->get();
        $history = DB::table('posisi_dozer_pc')->select('tanggal', 'shift')->distinct()->orderBy('tanggal', 'desc')->orderBy('shift', 'asc')->limit(30)->get();

        return Inertia::render('OneSetting/Legacy/SettingDozerPc', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_setting_menu'),
            'filters' => ['date' => $date, 'shift' => $shift],
            'rows' => $rows,
            'history' => $history,
        ]);
    }

    public function storeLegacyDozerPc(Request $request)
    {
        $date = $request->input('date');
        $shift = $request->input('shift');
        $raw = $request->input('raw_text', '');
        $data = [];

        $currentKategori = '';
        foreach (preg_split('/\r?\n/', $raw) as $line) {
            $line = trim(trim(trim($line), '_*'));
            if ($line === '') continue;

            if (preg_match('/^[-–]\s*(.+)$/u', $line, $m)) {
                $rest = trim($m[1]);
                if (strpos($rest, ':') === false) continue;
                [$labelRaw, $posisi] = array_map('trim', explode(':', $rest, 2));
                if ($labelRaw === '' || $posisi === '') continue;

                $ket = '';
                $label = $labelRaw;
                if (preg_match('/\(([^)]*)\)/', $label, $mx)) {
                    $ket   = trim($mx[1]);
                    $label = trim(preg_replace('/\([^)]*\)/', '', $label));
                }
                $clean = strtoupper(trim($label));

                if (preg_match('/^(DZ|EX)\.?\s*(.+)$/', $clean, $mx)) {
                    $prefix = $mx[1] === 'DZ' ? 'D' : 'E';
                    $rest2   = preg_replace('/[\s.]+/', '', $mx[2]);
                    $unit = preg_match('/^\d+[A-Z0-9]*$/', $rest2) ? $prefix . $rest2 : $rest2;
                } else {
                    $unit = preg_replace('/[\s.]+/', '', $clean);
                }

                if ($unit === '') continue;
                $data[$unit] = [
                    'kategori'   => $currentKategori,
                    'unit_label' => $labelRaw,
                    'posisi'     => $posisi,
                    'keterangan' => $ket,
                ];
            } else {
                $u = strtoupper($line);
                if (strpos($u, 'DOZER') !== false)   $currentKategori = 'DOZER';
                elseif (strpos($u, 'PC') !== false)  $currentKategori = 'PC';
            }
        }

        if (empty($data)) return back()->with('error', 'Tidak ada data.');

        DB::transaction(function() use ($data, $date, $shift) {
            DB::table('posisi_dozer_pc')->where('tanggal', $date)->where('shift', $shift)->delete();
            $ins = [];
            foreach ($data as $u => $v) {
                $ins[] = [
                    'tanggal' => $date, 'shift' => $shift, 'unit' => $u,
                    'kategori' => $v['kategori'], 'unit_label' => mb_substr($v['unit_label'],0,80),
                    'posisi' => mb_substr($v['posisi'],0,200), 'keterangan' => mb_substr($v['keterangan'],0,150)
                ];
            }
            DB::table('posisi_dozer_pc')->insert($ins);
        });

        return back()->with('success', count($data) . ' unit disimpan.');
    }

    public function deleteLegacyDozerPc($id)
    {
        DB::table('posisi_dozer_pc')->where('id', $id)->delete();
        return back()->with('success', 'Data dihapus.');
    }

    // --- PASSWORD CP ---

    public function legacyPwCp(Request $request)
    {
        $this->ensureLegacyTablesExist();
        $rows = DB::table('pw_speakup_cp')->orderBy('tanggal', 'desc')->orderBy('shift', 'asc')->get();

        return Inertia::render('OneSetting/Legacy/SettingPwCp', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_setting_menu'),
            'rows' => $rows,
        ]);
    }

    public function storeLegacyPwCp(Request $request)
    {
        $raw = $request->input('raw_text', '');
        $data = [];
        foreach (preg_split('/\r?\n/', $raw) as $line) {
            if (trim($line) === '') continue;
            $cols = (strpos($line, "\t") !== false)
                ? array_map('trim', explode("\t", $line))
                : preg_split('/\s{2,}/', trim($line));

            $dateIdx = -1; $tanggal = '';
            foreach ($cols as $i => $c) {
                $c = trim($c);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $c)) { $dateIdx = $i; $tanggal = $c; break; }
                if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $c, $m)) {
                    $dateIdx = $i; $tanggal = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]); break;
                }
            }
            if ($dateIdx < 0) continue;

            $shiftRaw = strtoupper(trim($cols[$dateIdx + 1] ?? ''));
            $password = trim($cols[$dateIdx + 2] ?? '');
            $shift = ($shiftRaw === 'II' || $shiftRaw === '2') ? '2'
                   : (($shiftRaw === 'I' || $shiftRaw === '1') ? '1' : '');

            if ($tanggal === '' || $shift === '' || $password === '') continue;
            $data[] = ['tanggal' => $tanggal, 'shift' => $shift, 'password' => $password];
        }

        if (empty($data)) return back()->with('error', 'Tidak ada data valid.');

        foreach ($data as $d) {
            DB::table('pw_speakup_cp')->updateOrInsert(
                ['tanggal' => $d['tanggal'], 'shift' => $d['shift']],
                ['password' => $d['password']]
            );
        }

        return back()->with('success', count($data) . ' password disimpan.');
    }

    public function deleteLegacyPwCp($id)
    {
        DB::table('pw_speakup_cp')->where('id', $id)->delete();
        return back()->with('success', 'Data dihapus.');
    }

    public function clearLegacyPwCp()
    {
        DB::table('pw_speakup_cp')->truncate();
        return back()->with('success', 'Semua data dihapus.');
    }
}
