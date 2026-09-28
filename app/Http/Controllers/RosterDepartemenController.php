<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\RosterMonthlyMaster;
use App\Services\GoogleSheetRosterImporter;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RosterDepartemenController extends Controller
{
    private const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    private const DAY_NAMES = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

    public const ROSTER_GROUPS = [
        'HD'          => 'Roster HD',
        'PC'          => 'Roster PC',
        'DZ'          => 'Roster DZ',
        'GD_VB_WL'    => 'Roster GD VB WL',
        'DT_WT_WT773' => 'Roster DT WT WT773',
        'LS_KJB'      => 'Roster LS KJB',
        'ACT_GL'      => 'Roster GL Produksi',
    ];

    public const DEFAULT_KEYWORDS_MAP = [
        'Roster HD'          => ['HD', '785', '777'],
        'Roster PC'          => ['PC', 'EXCAVATOR', '200', '1250', '2000', '3000', '400', '800'],
        'Roster DZ'          => ['DZ', 'DOZER', 'D85', 'D155', 'D375', 'DZ 155 A', 'DZ 85 SS'],
        'Roster GD VB WL'    => ['GD', 'GRADER', 'VB', 'VIBRO', 'WL', 'WHEEL LOADER', '825', '705', 'BOMAG', 'WA'],
        'Roster DT WT WT773' => ['DT', 'WT', '773', 'SCANIA', 'IVECO', 'VOLVO', 'HINO', 'FMX', 'FM'],
        'Roster LS KJB'      => ['LS', 'KJB', 'LIGHT VEHICLE', 'SARANA', 'ADMIN PRODUKSI', 'ICC PRODUKSI', 'HELPER PIT SERVICE', 'HELPER TRAFFICMAN', 'QC'],
        'Roster GL Produksi' => ['GL', 'LEADER', 'FOREMAN', 'SUPERVISOR'],
    ];

    public const KEYWORDS_MAP = self::DEFAULT_KEYWORDS_MAP;

    public function __construct(private GoogleSheetRosterImporter $importer) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        if (! $user?->canViewAllRosters()) {
            return redirect()->route('one-power.roster-personal.index');
        }

        $bulan = (int) $request->integer('bulan', now()->month);
        $bulan = ($bulan >= 1 && $bulan <= 12) ? $bulan : now()->month;
        $tahun = (int) $request->integer('tahun', now()->year);
        $category = trim((string) $request->string('category')) ?: null;
        $jabatanFilter = trim((string) $request->string('jabatan')) ?: null;
        $search = trim((string) $request->string('search')) ?: null;

        // Ambil URL Google Spreadsheet & kategori tersimpan terlebih dahulu
        $settingRow = DB::table('settings')->where('setting_key', 'roster_google_sheets')->first();
        $savedUrls = $settingRow && $settingRow->setting_value ? json_decode($settingRow->setting_value, true) : [];

        $catRow = DB::table('settings')->where('setting_key', 'roster_categories')->first();
        if ($catRow && $catRow->setting_value) {
            $categories = json_decode($catRow->setting_value, true);
        } else {
            $categories = [];
            foreach (self::ROSTER_GROUPS as $k => $label) {
                $categories[] = [
                    'key' => $k,
                    'label' => $label,
                    'url' => $savedUrls[$k] ?? '',
                    'keywords' => implode(', ', self::DEFAULT_KEYWORDS_MAP[$label] ?? []),
                ];
            }
            DB::table('settings')->updateOrInsert(
                ['setting_key' => 'roster_categories'],
                [
                    'setting_value' => json_encode($categories),
                    'updated_at' => now(),
                ]
            );
        }

        // Pastikan setiap kategori memiliki string keywords (bisa di-custom user)
        $keywordsMap = [];
        foreach ($categories as &$cat) {
            $label = $cat['label'] ?? '';
            if (! isset($cat['keywords']) || $cat['keywords'] === '') {
                $defaultKw = self::DEFAULT_KEYWORDS_MAP[$label] ?? [];
                $cat['keywords'] = is_array($defaultKw) ? implode(', ', $defaultKw) : (string) $defaultKw;
            }

            if (is_array($cat['keywords'])) {
                $keywordsMap[$label] = array_values(array_filter(array_map('trim', $cat['keywords'])));
            } else {
                $parts = explode(',', (string) $cat['keywords']);
                $keywordsMap[$label] = array_values(array_filter(array_map('trim', $parts)));
            }
        }
        unset($cat);

        $allGroupLabels = array_values(array_unique(array_filter(array_column($categories, 'label'))));
        if (empty($allGroupLabels)) {
            $allGroupLabels = array_values(self::ROSTER_GROUPS);
        }

        // Jika kategori yang sedang difilter sudah tidak ada/dihapus, otomatis reset ke null ("Semua Kategori")
        if ($category && ! in_array($category, $allGroupLabels, true)) {
            $category = null;
        }

        $groupMap = [];
        foreach ($categories as $cat) {
            $groupMap[$cat['key']] = $cat['label'];
        }

        $daysInMonth = (int) cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        // Daftar hari dalam bulan ini
        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $timestamp = mktime(0, 0, 0, $bulan, $d, $tahun);
            $dow = (int) date('w', $timestamp);
            $days[] = [
                'day' => $d,
                'dow' => self::DAY_NAMES[$dow],
                'isSunday' => ($dow === 0),
                'date' => sprintf('%04d-%02d-%02d', $tahun, $bulan, $d),
            ];
        }

        // Fetch distinct jabatan dari employees di roster bulan ini
        $jabatanQuery = DB::table('roster_monthly_master as r')
            ->join('employees as e', 'r.nrp', '=', 'e.nrp')
            ->whereMonth('r.tanggal', $bulan)
            ->whereYear('r.tanggal', $tahun)
            ->whereNotNull('e.jabatan_ss6')
            ->where('e.jabatan_ss6', '!=', '');

        if ($category) {
            $jabatanQuery->where(function ($q) use ($category, $keywordsMap) {
                $q->whereRaw('LOWER(TRIM(e.jabatan_roster)) = LOWER(?)', [trim($category)]);
                $keywords = $keywordsMap[$category] ?? [];
                if (! empty($keywords)) {
                    $q->orWhere(function ($sub) use ($keywords) {
                        $sub->where(function ($nullQ) {
                            $nullQ->whereNull('e.jabatan_roster')
                                  ->orWhere('e.jabatan_roster', '');
                        });
                        $sub->where(function ($kwQ) use ($keywords) {
                            foreach ($keywords as $kw) {
                                $kwQ->orWhereRaw('LOWER(e.jabatan_ss6) LIKE LOWER(?)', ['%' . $kw . '%']);
                            }
                        });
                    });
                }
            });
        }

        $jabatanList = $jabatanQuery->distinct()->orderBy('e.jabatan_ss6')->pluck('e.jabatan_ss6')->values()->toArray();

        // Query Utama Roster
        $query = DB::table('roster_monthly_master as r')
            ->join('employees as e', 'r.nrp', '=', 'e.nrp')
            ->whereMonth('r.tanggal', $bulan)
            ->whereYear('r.tanggal', $tahun)
            ->select([
                'r.id as entry_id',
                'r.nrp',
                'r.tanggal',
                'r.kode',
                'r.unit',
                'e.id as employee_id',
                'e.name as nama',
                'e.jabatan_ss6 as jabatan',
                'e.jabatan_roster',
            ]);

        if ($category) {
            $query->where(function ($q) use ($category, $keywordsMap) {
                $q->whereRaw('LOWER(TRIM(e.jabatan_roster)) = LOWER(?)', [trim($category)]);
                $keywords = $keywordsMap[$category] ?? [];
                if (! empty($keywords)) {
                    $q->orWhere(function ($sub) use ($keywords) {
                        $sub->where(function ($nullQ) {
                            $nullQ->whereNull('e.jabatan_roster')
                                  ->orWhere('e.jabatan_roster', '');
                        });
                        $sub->where(function ($kwQ) use ($keywords) {
                            foreach ($keywords as $kw) {
                                $kwQ->orWhereRaw('LOWER(e.jabatan_ss6) LIKE LOWER(?)', ['%' . $kw . '%']);
                            }
                        });
                    });
                }
            });
        }

        if ($jabatanFilter) {
            $query->where('e.jabatan_ss6', $jabatanFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('e.name', 'like', "%{$search}%")
                  ->orWhere('r.nrp', 'like', "%{$search}%");
            });
        }

        $rawRoster = $query->orderBy('e.name', 'asc')->orderBy('r.tanggal', 'asc')->get();

        // Build matrix & aggregations
        $matrix = [];
        $employeeInfo = [];

        foreach ($rawRoster as $r) {
            $nrp = (string) $r->nrp;
            if (! isset($matrix[$nrp])) {
                $matrix[$nrp] = array_fill(1, $daysInMonth, '');
                $employeeInfo[$nrp] = [
                    'employeeId' => $r->employee_id,
                    'nama' => $r->nama,
                    'jabatan' => $r->jabatan,
                    'jabatanRoster' => $r->jabatan_roster,
                ];
            }
            $day = (int) date('j', strtotime($r->tanggal));
            if ($day >= 1 && $day <= $daysInMonth) {
                $matrix[$nrp][$day] = (string) ($r->kode ?? '');
            }
        }

        // Summary per hari & grand total
        $codeTypes = [
            'D'   => 'SIANG',
            'N'   => 'MALAM',
            'OFF' => 'OFF',
            'CT'  => 'CUTI',
            'ID'  => 'INDUKSI',
            'IDS' => 'IDS (PERMIT)',
            'MCU' => 'MCU',
            'FU'  => 'FU (FOLLOW UP)',
            'H'   => 'H (DIRUMAHKAN)',
            'TR'  => 'TRAINING',
        ];

        $akkuPerHari = [];
        foreach (array_keys($codeTypes) as $k) {
            $akkuPerHari[$k] = array_fill(1, $daysInMonth, 0);
        }

        $akkuTotalHari = array_fill(1, $daysInMonth, 0);

        // Akumulasi Per Karyawan
        $employeeRows = [];
        $grandSummary = [
            'siang' => 0, 'malam' => 0, 'off' => 0, 'cuti' => 0,
            'induksi' => 0, 'mcu' => 0, 'fu' => 0, 'h' => 0,
            'training' => 0, 'total' => 0,
        ];

        foreach ($matrix as $nrp => $daysData) {
            $s = $n = $off = $ct = $ind = $mcu = $fu = $h = $tr = $tot = 0;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $k = $daysData[$d];
                if ($k !== '') {
                    $akkuTotalHari[$d]++;
                    if (isset($akkuPerHari[$k])) {
                        $akkuPerHari[$k][$d]++;
                    }

                    $tot++;
                    if ($k === 'D') $s++;
                    elseif ($k === 'N') $n++;
                    elseif ($k === 'OFF') $off++;
                    elseif ($k === 'CT') $ct++;
                    elseif ($k === 'ID' || $k === 'IDS') $ind++;
                    elseif ($k === 'MCU') $mcu++;
                    elseif ($k === 'FU') $fu++;
                    elseif ($k === 'H') $h++;
                    elseif ($k === 'TR') $tr++;
                }
            }

            $grandSummary['siang'] += $s;
            $grandSummary['malam'] += $n;
            $grandSummary['off'] += $off;
            $grandSummary['cuti'] += $ct;
            $grandSummary['induksi'] += $ind;
            $grandSummary['mcu'] += $mcu;
            $grandSummary['fu'] += $fu;
            $grandSummary['h'] += $h;
            $grandSummary['training'] += $tr;
            $grandSummary['total'] += $tot;

            $employeeRows[] = [
                'employeeId' => $employeeInfo[$nrp]['employeeId'],
                'nrp' => $nrp,
                'nama' => $employeeInfo[$nrp]['nama'],
                'jabatan' => $employeeInfo[$nrp]['jabatan'],
                'jabatanRoster' => $employeeInfo[$nrp]['jabatanRoster'],
                'days' => $daysData,
                'summary' => [
                    'siang' => $s,
                    'malam' => $n,
                    'off' => $off,
                    'cuti' => $ct,
                    'induksi' => $ind,
                    'mcu' => $mcu,
                    'fu' => $fu,
                    'h' => $h,
                    'training' => $tr,
                    'total' => $tot,
                ],
            ];
        }

        $manpowerEmployees = Employee::query()
            ->where(function ($q) {
                $q->where('jabatan_ss6', 'like', '%manpower%')
                  ->orWhere('jabatan_roster', 'like', '%manpower%');
            })
            ->where(function ($q) {
                $q->whereNull('status_karyawan')
                  ->orWhere('status_karyawan', 'not like', '%inactive%');
            })
            ->orderBy('name')
            ->get(['nrp', 'name', 'jabatan_ss6'])
            ->map(fn ($e) => [
                'nrp' => (string) $e->nrp,
                'name' => (string) $e->name,
                'jabatan' => (string) ($e->jabatan_ss6 ?: 'Group Leader Manpower'),
            ])
            ->values()
            ->all();

        return Inertia::render('OnePower/RosterDepartemen/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'filters' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'category' => $category,
                'jabatan' => $jabatanFilter,
                'search' => $search,
                'monthNames' => self::MONTH_NAMES,
                'years' => range(now()->year - 2, now()->year + 1),
            ],
            'daysInMonth' => $daysInMonth,
            'days' => $days,
            'rosterGroups' => $allGroupLabels,
            'rosterGroupsMap' => $groupMap,
            'categories' => $categories,
            'savedGoogleSheetUrls' => $savedUrls,
            'jabatanList' => $jabatanList,
            'manpowerEmployees' => $manpowerEmployees,
            'rows' => $employeeRows,
            'akkuPerHari' => $akkuPerHari,
            'akkuTotalHari' => $akkuTotalHari,
            'codeTypes' => $codeTypes,
            'grandSummary' => $grandSummary,
        ]);
    }

    public function updateCell(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nrp' => ['required', 'string'],
            'tanggal' => ['required', 'date'],
            'kode' => ['nullable', 'string', 'max:20'],
        ]);

        $kode = $data['kode'] !== null ? strtoupper(trim($data['kode'])) : null;

        if ($kode === null || $kode === '') {
            RosterMonthlyMaster::where('nrp', $data['nrp'])
                ->where('tanggal', $data['tanggal'])
                ->delete();
        } else {
            RosterMonthlyMaster::updateOrCreate(
                ['nrp' => $data['nrp'], 'tanggal' => $data['tanggal']],
                ['kode' => $kode]
            );
        }

        return redirect()->back()->with('success', 'Roster karyawan berhasil diperbarui.');
    }

    public function saveGoogleSheetUrls(Request $request): RedirectResponse
    {
        $categories = $request->input('categories', []);

        if (is_array($categories)) {
            $cleanedCategories = array_values($categories);

            // Populate nama dan jabatan penanggung jawab berdasarkan NRP jika belum ada atau diperbarui
            $pjNrps = array_values(array_filter(array_column($cleanedCategories, 'penanggung_jawab_nrp')));
            if (! empty($pjNrps)) {
                $empMap = Employee::whereIn('nrp', $pjNrps)->get(['nrp', 'name', 'jabatan_ss6'])->keyBy('nrp');
                foreach ($cleanedCategories as &$cat) {
                    $nrp = $cat['penanggung_jawab_nrp'] ?? '';
                    if ($nrp && isset($empMap[$nrp])) {
                        $cat['penanggung_jawab_nama'] = $empMap[$nrp]->name;
                        $cat['penanggung_jawab_jabatan'] = $empMap[$nrp]->jabatan_ss6 ?: 'Group Leader Manpower';
                    } elseif (! $nrp) {
                        $cat['penanggung_jawab_nama'] = '';
                        $cat['penanggung_jawab_jabatan'] = '';
                    }
                }
                unset($cat);
            }

            DB::table('settings')->updateOrInsert(
                ['setting_key' => 'roster_categories'],
                [
                    'setting_value' => json_encode($cleanedCategories),
                    'updated_at' => now(),
                ]
            );

            // Sync URLs murni dari kategori yang tersisa saat ini
            $syncedUrls = [];
            foreach ($cleanedCategories as $cat) {
                if (! empty($cat['key']) && ! empty($cat['url'])) {
                    $syncedUrls[$cat['key']] = $cat['url'];
                }
            }

            DB::table('settings')->updateOrInsert(
                ['setting_key' => 'roster_google_sheets'],
                [
                    'setting_value' => json_encode($syncedUrls),
                    'updated_at' => now(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Pengaturan kategori & URL Google Spreadsheet berhasil disimpan.');
    }

    public function importGoogleSheet(Request $request): JsonResponse
    {
        $groupKey = $request->input('group');
        $sheetUrl = trim((string) $request->input('url', ''));
        $groupLabel = trim((string) $request->input('label', ''));

        // Import grup tunggal
        if ($groupKey || $groupLabel) {
            $label = $groupLabel ?: ($groupKey && isset(self::ROSTER_GROUPS[$groupKey]) ? self::ROSTER_GROUPS[$groupKey] : (string) $groupKey);
            if (! $sheetUrl && $groupKey) {
                $settingRow = DB::table('settings')->where('setting_key', 'roster_google_sheets')->first();
                $saved = $settingRow && $settingRow->setting_value ? json_decode($settingRow->setting_value, true) : [];
                $sheetUrl = $saved[$groupKey] ?? '';
            }

            if (! $sheetUrl) {
                return response()->json(['success' => false, 'error' => "URL Google Sheet untuk {$label} belum diisi."], 422);
            }

            $res = $this->importer->importFromUrl($sheetUrl, $label);

            return response()->json($res, $res['success'] ? 200 : 422);
        }

        // Import semua kategori yang ada URL-nya
        $catRow = DB::table('settings')->where('setting_key', 'roster_categories')->first();
        $savedCategories = $catRow && $catRow->setting_value ? json_decode($catRow->setting_value, true) : [];

        $targetList = [];
        if (! empty($savedCategories) && is_array($savedCategories)) {
            foreach ($savedCategories as $cat) {
                $url = trim((string) ($cat['url'] ?? ''));
                if (! empty($url)) {
                    $targetList[] = [
                        'label' => $cat['label'] ?? ($cat['key'] ?? 'Roster'),
                        'url' => $url,
                    ];
                }
            }
        } else {
            $urlsInput = $request->input('urls');
            if (! is_array($urlsInput) || empty($urlsInput)) {
                $settingRow = DB::table('settings')->where('setting_key', 'roster_google_sheets')->first();
                $urlsInput = $settingRow && $settingRow->setting_value ? json_decode($settingRow->setting_value, true) : [];
            }

            foreach (self::ROSTER_GROUPS as $key => $label) {
                $url = trim((string) ($urlsInput[$key] ?? ''));
                if (! empty($url)) {
                    $targetList[] = [
                        'label' => $label,
                        'url' => $url,
                    ];
                }
            }
        }

        $totalCount = 0;
        $summary = [];
        $errors = [];

        foreach ($targetList as $item) {
            $res = $this->importer->importFromUrl($item['url'], $item['label']);
            if ($res['success']) {
                $totalCount += $res['count'];
                $summary[$item['label']] = $res['count'];
            } else {
                $errors[$item['label']] = $res['error'];
            }
        }

        if (empty($summary) && ! empty($errors)) {
            return response()->json([
                'success' => false,
                'errors' => $errors,
                'message' => 'Gagal mengimpor Google Spreadsheet. Periksa izin akses (Anyone with the link).',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'count' => $totalCount,
            'summary' => $summary,
            'errors' => $errors,
            'message' => "Update selesai: Berhasil memperbarui {$totalCount} data roster.",
        ]);
    }

    public function importFile(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'category' => ['nullable', 'string'],
        ]);

        $res = $this->importer->importFromFile($request->file('file'), $request->input('category'));

        if (! $res['success']) {
            return redirect()->back()->with('error', $res['error']);
        }

        return redirect()->back()->with('success', $res['message']);
    }
}
