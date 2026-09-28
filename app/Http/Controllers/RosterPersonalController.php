<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\RosterService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RosterPersonalController extends Controller
{
    private const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function __construct(private RosterService $rosterService) {}

    /**
     * Roster kalender 1 karyawan. Admin bebas pilih NRP manapun via ?nrp=,
     * non-admin dipaksa NRP miliknya sendiri (mengikuti V1: $isAdmin ? $_GET['nrp'] : $_SESSION['username']).
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $canViewAll = (bool) $user?->canViewAllRosters();
        $isAdmin = $canViewAll;
        $isPrivileged = $canViewAll
            || ($user && in_array(strtolower((string) $user->role), ['admin', 'pimpinan', 'gl_manpower', 'admin_produksi', 'management'], true))
            || ($user && str_contains(strtolower((string) $user->jabatan), 'admin produksi'));

        // Ambil pengaturan visibilitas bulan dari settings (V1 compat: roster_visible_months)
        $settingRow = DB::table('settings')->where('setting_key', 'roster_visible_months')->first();
        $visibleMonths = [];
        if ($settingRow && ! empty($settingRow->setting_value)) {
            $decoded = json_decode($settingRow->setting_value, true);
            if (is_array($decoded)) {
                $visibleMonths = array_values(array_filter(array_map('intval', $decoded), fn ($m) => $m >= 1 && $m <= 12));
            }
        }
        if (empty($visibleMonths)) {
            $visibleMonths = range(1, 12);
        }

        $availableMonthNames = self::MONTH_NAMES;
        if (! $isPrivileged) {
            $availableMonthNames = array_intersect_key(self::MONTH_NAMES, array_flip($visibleMonths));
            if (empty($availableMonthNames)) {
                $availableMonthNames = [now()->month => self::MONTH_NAMES[now()->month]];
            }
        }

        $month = (int) $request->integer('bulan', now()->month);
        $month = $month >= 1 && $month <= 12 ? $month : now()->month;
        if (! $isPrivileged && ! in_array($month, $visibleMonths, true)) {
            $month = array_key_first($availableMonthNames);
        }

        $year = (int) $request->integer('tahun', now()->year);

        $selectedNrp = $canViewAll
            ? trim((string) $request->string('nrp'))
            : $this->ownNrp($user);

        if ($canViewAll && $selectedNrp === '') {
            $selectedNrp = $this->ownNrp($user);
            if (! $selectedNrp) {
                $firstMaster = DB::table('roster_monthly_master')
                    ->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year)
                    ->value('nrp');
                $selectedNrp = (string) ($firstMaster ?: '');
            }
        }

        $calendar = $selectedNrp
            ? $this->rosterService->personalCalendar($selectedNrp, $year, $month)
            : ['employee' => null, 'days' => []];

        $employeeOptions = [];
        if ($canViewAll) {
            $masterList = DB::table('roster_monthly_master as r')
                ->join('employees as e', 'r.nrp', '=', 'e.nrp')
                ->whereMonth('r.tanggal', $month)
                ->whereYear('r.tanggal', $year)
                ->select(['r.nrp', 'e.name', 'e.jabatan_ss6', 'r.unit'])
                ->distinct()
                ->orderBy('e.name')
                ->get();

            if ($masterList->isNotEmpty()) {
                $employeeOptions = $masterList;
            } else {
                $employeeOptions = DB::table('employees')
                    ->where('status', 'aktif')
                    ->orderBy('name')
                    ->limit(300)
                    ->get(['nrp', 'name', 'jabatan_ss6', 'unit']);
            }
        }

        return Inertia::render('OnePower/Roster/Personal', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'isAdmin' => $isAdmin,
            'canViewAll' => $canViewAll,
            'employeeOptions' => $employeeOptions,
            'filters' => [
                'nrp' => $selectedNrp,
                'bulan' => $month,
                'tahun' => $year,
                'monthNames' => $availableMonthNames,
                'allMonthNames' => self::MONTH_NAMES,
                'visibleMonths' => $visibleMonths,
                'years' => range(now()->year - 2, now()->year + 1),
            ],
            'employee' => $calendar['employee'],
            'days' => $calendar['days'],
            'penanggungJawab' => $this->resolvePenanggungJawab($selectedNrp),
        ]);
    }

    /**
     * Simpan pengaturan visibilitas bulan untuk user non-admin (Ops/Helper).
     */
    public function saveVisibleMonths(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $canManage = (bool) $user?->canViewAllRosters();

        abort_unless($canManage, 403, 'Akses ditolak. Hanya Admin, Software Engineer, Section Head, & Dept Head yang dapat mengatur visibilitas bulan.');

        $months = $request->input('months', []);
        if (! is_array($months)) {
            $months = [];
        }
        $months = array_values(array_unique(array_filter(array_map('intval', $months), fn ($m) => $m >= 1 && $m <= 12)));
        sort($months);

        DB::table('settings')->updateOrInsert(
            ['setting_key' => 'roster_visible_months'],
            [
                'setting_value' => json_encode($months),
                'updated_at' => now(),
            ]
        );

        if ($request->header('X-Inertia')) {
            return redirect()->back()->with('success', 'Visibilitas bulan berhasil disimpan.');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Visibilitas bulan berhasil disimpan.',
            'visibleMonths' => $months,
        ]);
    }

    /**
     * Autocomplete NRP/nama untuk picker admin. Non-admin tidak butuh ini
     * (tidak bisa pilih karyawan lain), tapi endpoint tetap role-gated untuk berjaga-jaga.
     */
    public function searchEmployee(Request $request): JsonResponse
    {
        abort_unless((bool) $request->user()?->canViewAllRosters(), 403);

        $keyword = trim((string) $request->string('keyword'));

        $query = Employee::query();
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('nrp', 'like', "%{$keyword}%")
                  ->orWhere('name', 'like', "%{$keyword}%");
            });
        }

        return response()->json(
            $query->orderBy('name')->limit(50)->get(['id', 'nrp', 'name', 'jabatan_ss6', 'unit'])
        );
    }

    /**
     * Cetak Roster Personal sebagai PDF (Browsershot) atau halaman cetak HTML (seperti di Formulir DMS).
     */
    public function printRosterPdf(Request $request)
    {
        $user = $request->user();
        $canViewAll = (bool) $user?->canViewAllRosters();

        $month = (int) $request->integer('bulan', now()->month);
        $month = $month >= 1 && $month <= 12 ? $month : now()->month;
        $year = (int) $request->integer('tahun', now()->year);

        $selectedNrp = $canViewAll
            ? trim((string) $request->string('nrp'))
            : $this->ownNrp($user);

        if ($canViewAll && $selectedNrp === '') {
            $selectedNrp = $this->ownNrp($user);
            if (! $selectedNrp) {
                $firstMaster = DB::table('roster_monthly_master')
                    ->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year)
                    ->value('nrp');
                $selectedNrp = (string) ($firstMaster ?: '');
            }
        }

        $calendar = $selectedNrp
            ? $this->rosterService->personalCalendar($selectedNrp, $year, $month)
            : ['employee' => null, 'days' => []];

        $employee = $calendar['employee'];
        $days = $calendar['days'];
        $monthLabel = self::MONTH_NAMES[$month] ?? '';

        $logoPath = public_path('images/images.png');
        $logoDataUri = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : '';

        $safeStrongPath = public_path('images/logo_safe_strong.png');
        $safeStrongDataUri = file_exists($safeStrongPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($safeStrongPath))
            : '';

        $synergyPath = public_path('images/logo_synergy.png');
        $synergyDataUri = file_exists($synergyPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($synergyPath))
            : '';

        $manifestPath = public_path('build/manifest.json');
        $inlineCss = '';
        if (file_exists($manifestPath)) {
            $manifest = json_decode((string) file_get_contents($manifestPath), true);
            if (isset($manifest['resources/css/app.css']['file'])) {
                $appCssPath = public_path('build/' . $manifest['resources/css/app.css']['file']);
                if (file_exists($appCssPath)) {
                    $inlineCss = (string) file_get_contents($appCssPath);
                }
            }
        }

        $pj = $this->resolvePenanggungJawab($selectedNrp);
        $defaultSignerName = $pj['nama'] ?? 'Radhitya HS';
        $defaultSignerTitle = $pj['jabatan'] ?? 'Group Leader Manpower';

        $reqSignerName = trim((string) $request->string('signer_name'));
        $reqSignerTitle = trim((string) $request->string('signer_title'));

        $signerName = ($reqSignerName !== '' && $reqSignerName !== 'Radhitya HS') ? $reqSignerName : $defaultSignerName;
        $signerTitle = ($reqSignerTitle !== '' && $reqSignerTitle !== 'Group Leader Manpower') ? $reqSignerTitle : $defaultSignerTitle;

        $html = view('pdf.roster-personal', [
            'employee' => $employee,
            'days' => $days,
            'bulan' => $month,
            'tahun' => $year,
            'monthLabel' => $monthLabel,
            'signerName' => $signerName,
            'signerTitle' => $signerTitle,
            'logoDataUri' => $logoDataUri,
            'safeStrongDataUri' => $safeStrongDataUri,
            'synergyDataUri' => $synergyDataUri,
            'inlineCss' => $inlineCss,
        ])->render();

        try {
            $browsershot = \Spatie\Browsershot\Browsershot::html($html)
                ->noSandbox()
                ->showBackground()
                ->windowSize(1200, 1600)
                ->paperSize(210, 297, 'mm')
                ->margins(8, 8, 8, 8, 'mm');

            if ($chromePath = config('services.browsershot.chrome_path')) {
                $browsershot->setChromePath($chromePath);
            }
            if ($nodeBinary = config('services.browsershot.node_binary')) {
                $browsershot->setNodeBinary($nodeBinary);
            }
            if ($npmBinary = config('services.browsershot.npm_binary')) {
                $browsershot->setNpmBinary($npmBinary);
            }

            $pdf = $browsershot->pdf();

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"roster-personal-{$selectedNrp}-{$year}-{$month}.pdf\"",
            ]);
        } catch (\Throwable $e) {
            return response($html, 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]);
        }
    }

    private function resolvePenanggungJawab(?string $nrp): ?array
    {
        if (! $nrp) {
            return null;
        }

        $catRow = DB::table('settings')->where('setting_key', 'roster_categories')->first();
        $categories = $catRow && $catRow->setting_value ? json_decode($catRow->setting_value, true) : [];
        if (empty($categories) || ! is_array($categories)) {
            return null;
        }

        $empObj = Employee::where('nrp', $nrp)->first();
        if (! $empObj) {
            return null;
        }

        $empJabatanRoster = strtolower(trim((string) ($empObj->jabatan_roster ?? '')));
        $empJabatanSs6 = strtolower(trim((string) ($empObj->jabatan_ss6 ?? '')));

        $empUnit = '';
        $latestUnit = DB::table('roster_monthly_master')
            ->where('nrp', $nrp)
            ->whereNotNull('unit')
            ->where('unit', '!=', '')
            ->orderByDesc('tanggal')
            ->value('unit');
        if ($latestUnit) {
            $empUnit = strtolower(trim((string) $latestUnit));
        }

        // 1. Cek kecocokan jabatan_roster dengan label atau key kategori
        if ($empJabatanRoster !== '') {
            foreach ($categories as $cat) {
                if (! empty($cat['penanggung_jawab_nama'])) {
                    $catLabel = strtolower(trim((string) ($cat['label'] ?? '')));
                    $catKey = strtolower(trim((string) ($cat['key'] ?? '')));
                    if (
                        ($catLabel !== '' && ($catLabel === $empJabatanRoster || str_contains($empJabatanRoster, $catLabel) || str_contains($catLabel, $empJabatanRoster))) ||
                        ($catKey !== '' && ($catKey === $empJabatanRoster || str_contains($empJabatanRoster, $catKey)))
                    ) {
                        return [
                            'nrp' => $cat['penanggung_jawab_nrp'] ?? '',
                            'nama' => $cat['penanggung_jawab_nama'],
                            'jabatan' => $cat['penanggung_jawab_jabatan'] ?? 'Group Leader Manpower',
                        ];
                    }
                }
            }
        }

        // 2. Cek kecocokan keywords terhadap jabatan_ss6, jabatan_roster, atau unit
        foreach ($categories as $cat) {
            if (! empty($cat['penanggung_jawab_nama'])) {
                $kwStr = (string) ($cat['keywords'] ?? '');
                $kws = is_array($cat['keywords'] ?? null) ? $cat['keywords'] : explode(',', $kwStr);
                foreach ($kws as $kw) {
                    $trimmedKw = strtolower(trim($kw));
                    if ($trimmedKw !== '' && (
                        str_contains($empJabatanSs6, $trimmedKw) ||
                        str_contains($empJabatanRoster, $trimmedKw) ||
                        ($empUnit !== '' && str_contains($empUnit, $trimmedKw))
                    )) {
                        return [
                            'nrp' => $cat['penanggung_jawab_nrp'] ?? '',
                            'nama' => $cat['penanggung_jawab_nama'],
                            'jabatan' => $cat['penanggung_jawab_jabatan'] ?? 'Group Leader Manpower',
                        ];
                    }
                }
            }
        }

        return null;
    }

    private function ownNrp($user): string
    {
        if (! $user) {
            return '';
        }

        return $user->employee?->nrp ?? $user->username ?? '';
    }
}
