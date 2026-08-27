<?php

namespace App\Http\Controllers;

use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MonitoringGradeController extends Controller
{
    public function index(Request $request): Response
    {
        $tab = $request->string('tab', 'grade')->toString();
        $search = trim($request->string('q')->toString());
        $perPage = max(10, min(200, (int) $request->input('per', 50)));

        $gradeData = [];
        $versatilityData = [];
        $monitoringData = [];
        $totalRows = 0;

        if ($tab === 'grade') {
            $query = DB::table('odp_mg_grade_v4');
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'LIKE', "%{$search}%")
                        ->orWhere('nrp', 'LIKE', "%{$search}%")
                        ->orWhere('posisi', 'LIKE', "%{$search}%");
                });
            }
            $totalRows = $query->count();
            $gradeData = $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();
        } elseif ($tab === 'versality') {
            $query = DB::table('odp_mg_versality_v5');
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'LIKE', "%{$search}%")
                        ->orWhere('nrp', 'LIKE', "%{$search}%")
                        ->orWhere('jabatan', 'LIKE', "%{$search}%")
                        ->orWhere('versality', 'LIKE', "%{$search}%");
                });
            }
            $totalRows = $query->count();
            $versatilityData = $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();
        } elseif ($tab === 'monitoring') {
            $positions = DB::table('odp_mg_grade_v4')
                ->whereNotNull('posisi')
                ->where('posisi', '!=', '')
                ->when($search !== '', fn ($q) => $q->where('posisi', 'LIKE', "%{$search}%"))
                ->distinct()
                ->pluck('posisi');

            $totalRows = $positions->count();

            foreach ($positions as $posisi) {
                $records = DB::table('odp_mg_grade_v4')
                    ->where('posisi', $posisi)
                    ->select('suite', 'grade_now')
                    ->get();

                $actual = $records->count();
                $sesuai = 0;
                $belumSesuai = 0;
                $unsuitableGrades = [];

                foreach ($records as $rec) {
                    $suite = strtoupper(trim((string) $rec->suite));
                    $gradeNow = trim((string) $rec->grade_now);

                    if ($suite === 'YES') {
                        $sesuai++;
                    } else {
                        $belumSesuai++;
                        if ($gradeNow !== '') {
                            $unsuitableGrades[] = $gradeNow;
                        }
                    }
                }

                $monitoringData[] = [
                    'type_unit' => $posisi,
                    'actual_operator' => $actual,
                    'grade_sesuai' => $sesuai,
                    'grade_belum_sesuai' => $belumSesuai,
                    'ket' => implode(', ', array_unique($unsuitableGrades)),
                ];
            }
        }

        return Inertia::render('OneAcademy/MonitoringGrade/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_academy_menu'),
            'tab' => $tab,
            'search' => $search,
            'perPage' => $perPage,
            'totalRows' => $totalRows,
            'gradeData' => $gradeData,
            'versatilityData' => $versatilityData,
            'monitoringData' => $monitoringData,
        ]);
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

        DB::table('odp_mg_grade_v4')->insert(array_merge($data, ['created_at' => now(), 'updated_at' => now()]));

        return back()->with('success', 'Data Grade berhasil ditambahkan.');
    }

    public function storeVersatility(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nrp' => 'required|string',
            'nama' => 'required|string',
            'jabatan' => 'nullable|string',
            'versality' => 'nullable|string',
        ]);

        DB::table('odp_mg_versality_v5')->insert(array_merge($data, ['created_at' => now(), 'updated_at' => now()]));

        return back()->with('success', 'Data Versatility berhasil ditambahkan.');
    }
}
