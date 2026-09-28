<?php

namespace App\Http\Controllers;

use App\Services\MonitoringGradeSyncService;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MonitoringGradeController extends Controller
{
    private const TABLES = ['grade' => 'odp_mg_grade_v4', 'versality' => 'odp_mg_versality_v5'];

    public function __construct(private MonitoringGradeSyncService $sync) {}

    public function index(Request $request): Response
    {
        $tab = in_array($request->string('tab')->toString(), ['grade', 'versality', 'monitoring'], true)
            ? $request->string('tab')->toString() : 'grade';
        $search = trim($request->string('q')->toString());
        $perPage = max(10, min(200, (int) $request->input('per', 50)));
        $page = max(1, (int) $request->input('page', 1));

        $columns = config("monitoring_grade.{$tab}");
        [$rows, $totalRows, $pageFilterValues, $activeFilters] = $tab === 'monitoring'
            ? $this->buildMonitoringRows($request, $search, $page, $perPage)
            : $this->buildTableRows($request, $tab, $columns, $search, $page, $perPage);

        return Inertia::render('OneAcademy/MonitoringGrade/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_academy_menu'),
            'tab' => $tab,
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'totalRows' => $totalRows,
            'rows' => $rows,
            'columns' => $columns,
            'versalityGroups' => config('monitoring_grade.versality_groups'),
            'pageFilterValues' => $pageFilterValues,
            'activeFilters' => $activeFilters,
            'sheetUrls' => [
                'grade' => $this->sync->getStoredUrl('grade_url'),
                'versality' => $this->sync->getStoredUrl('versality_url'),
            ],
        ]);
    }

    /** @return array{0: Collection, 1: int, 2: array<string, Collection>, 3: array<string, array>} */
    private function buildTableRows(Request $request, string $tab, array $columns, string $search, int $page, int $perPage): array
    {
        $table = self::TABLES[$tab];
        $searchColumns = config("monitoring_grade.{$tab}_search_columns");

        $query = DB::table($table)->when($search !== '', function ($q) use ($searchColumns, $search) {
            $q->where(function ($sub) use ($searchColumns, $search) {
                foreach ($searchColumns as $i => $col) {
                    $i === 0 ? $sub->where($col, 'like', "%{$search}%") : $sub->orWhere($col, 'like', "%{$search}%");
                }
            });
        });

        $activeFilters = [];
        foreach ($columns as $col) {
            $key = $col['key'];
            $raw = $request->string("filter_{$key}")->toString();
            if ($raw === '') {
                continue;
            }
            $values = array_values(array_filter(array_map('trim', explode(',', $raw)), fn ($v) => $v !== ''));
            if ($values) {
                $query->whereIn($key, $values);
                $activeFilters[$key] = $values;
            }
        }

        // Satu fetch untuk seluruh baris yang cocok search+filter - dipakai baik utk
        // menghitung opsi dropdown filter (nilai unik dlm scope saat ini) maupun utk
        // slice halaman tampilan, supaya tidak query database dua kali.
        $filteredRows = $query->orderBy('id')->get();
        $totalRows = $filteredRows->count();

        $pageFilterValues = [];
        foreach ($columns as $col) {
            $key = $col['key'];
            $pageFilterValues[$key] = $filteredRows->pluck($key)
                ->map(fn ($v) => trim((string) $v))
                ->filter(fn ($v) => $v !== '')
                ->unique()
                ->sort()
                ->values();
        }

        $rows = collect($filteredRows->forPage($page, $perPage)->values());

        return [$rows, $totalRows, $pageFilterValues, $activeFilters];
    }

    /** @return array{0: Collection, 1: int, 2: array<string, Collection>, 3: array<string, array>} */
    private function buildMonitoringRows(Request $request, string $search, int $page, int $perPage): array
    {
        $allPositions = DB::table('odp_mg_grade_v4')
            ->whereNotNull('posisi')->where('posisi', '!=', '')
            ->distinct()->orderBy('posisi')->pluck('posisi');

        $positions = $search !== ''
            ? $allPositions->filter(fn ($p) => str_contains(strtolower($p), strtolower($search)))->values()
            : $allPositions;

        $activeFilters = [];
        $filterRaw = $request->string('filter_type_unit')->toString();
        if ($filterRaw !== '') {
            $values = array_values(array_filter(array_map('trim', explode(',', $filterRaw)), fn ($v) => $v !== ''));
            if ($values) {
                $positions = $positions->filter(fn ($p) => in_array($p, $values, true))->values();
                $activeFilters['type_unit'] = $values;
            }
        }

        $totalRows = $positions->count();
        $positionsPage = $positions->forPage($page, $perPage)->values();

        $rows = $positionsPage->map(function ($posisi) {
            $records = DB::table('odp_mg_grade_v4')->where('posisi', $posisi)->select('suite', 'grade_now')->get();

            $sesuai = 0;
            $belumSesuai = 0;
            $unsuitableGrades = [];
            foreach ($records as $rec) {
                if (strtoupper(trim((string) $rec->suite)) === 'YES') {
                    $sesuai++;
                } else {
                    $belumSesuai++;
                    $gradeNow = trim((string) $rec->grade_now);
                    if ($gradeNow !== '') {
                        $unsuitableGrades[] = $gradeNow;
                    }
                }
            }

            return [
                'type_unit' => $posisi,
                'actual_operator' => $records->count(),
                'grade_sesuai' => $sesuai,
                'grade_belum_sesuai' => $belumSesuai,
                'ket' => implode(', ', array_unique($unsuitableGrades)),
            ];
        });

        $pageFilterValues = ['type_unit' => $allPositions];

        return [$rows, $totalRows, $pageFilterValues, $activeFilters];
    }

    public function storeGrade(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nrp' => 'required|string',
            'nama' => 'required|string',
            'posisi' => 'nullable|string',
            'eg' => 'nullable|string',
            'sub_area' => 'nullable|string',
            'atr_6_bulan' => 'nullable|string',
            'join_date' => 'nullable|string',
            'efektif_grade' => 'nullable|string',
            'efektif_position' => 'nullable|string',
            'grade_now' => 'nullable|string',
            'min_level' => 'nullable|string',
            'max_level' => 'nullable|string',
            'status_level' => 'nullable|string',
            'suite' => 'nullable|string',
            'level_posisi' => 'nullable|string',
            'jumlah_versatility' => 'nullable|string',
            'requirement_versatility' => 'nullable|string',
            'gap_versatility' => 'nullable|string',
            'kesesuaian_versatility' => 'nullable|string',
            'masa_kerja' => 'nullable|string',
            'masa_kerja_grade_sekarang' => 'nullable|string',
            'masa_kerja_posisi_sekarang' => 'nullable|string',
            'masa_grade_3_th_up' => 'nullable|string',
            'masa_position_3th_up' => 'nullable|string',
            'sertifikasi' => 'nullable|string',
            'status_sertifikasi' => 'nullable|string',
            'expired_date_sertifikasi' => 'nullable|string',
            'remaining_time' => 'nullable|string',
            'remarks_grade' => 'nullable|string',
        ]);

        DB::table('odp_mg_grade_v4')->insert([...$data, 'created_at' => now(), 'updated_at' => now()]);

        return back()->with('success', 'Data Grade berhasil ditambahkan.');
    }

    public function storeVersatility(Request $request): RedirectResponse
    {
        $rules = ['nrp' => 'required|string', 'nama' => 'required|string', 'jabatan' => 'nullable|string', 'versality' => 'nullable|string'];
        foreach (config('monitoring_grade.versality') as $col) {
            if (str_starts_with($col['key'], 'grp')) {
                $rules[$col['key']] = 'nullable|string';
            }
        }

        $data = $request->validate($rules);

        DB::table('odp_mg_versality_v5')->insert([...$data, 'created_at' => now(), 'updated_at' => now()]);

        return back()->with('success', 'Data Versatility berhasil ditambahkan.');
    }

    public function syncGrade(Request $request): RedirectResponse
    {
        $error = $this->sync->syncGrade($request->input('sheets_url') ?: null);

        return $error
            ? back()->withErrors(['sheets_url' => $error])
            : redirect()->route('one-academy.monitoring-grade.index', ['tab' => 'grade'])->with('success', 'Data Grade berhasil disinkronkan.');
    }

    public function syncVersatility(Request $request): RedirectResponse
    {
        $error = $this->sync->syncVersatility($request->input('sheets_url') ?: null);

        return $error
            ? back()->withErrors(['sheets_url' => $error])
            : redirect()->route('one-academy.monitoring-grade.index', ['tab' => 'versality'])->with('success', 'Data Versatility berhasil disinkronkan.');
    }
}
