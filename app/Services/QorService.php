<?php

namespace App\Services;

use App\Models\MorRanking;
use App\Models\QorDpr;
use App\Models\QorPdty;
use App\Models\QorPerforma;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QorService
{
    public function __construct(
        private QorPdtyService $pdtyService,
        private MorRankingService $morService,
        private QorImportService $importService
    ) {}

    public static function quartalRange(int $year, int $quarter): array
    {
        return QorPdtyService::quarterRange($year, $quarter);
    }

    public function getOperatorReport(int $year, int $quarter, ?string $search = null, string $kategori = 'ALL', int $perPage = 50)
    {
        [$start, $end] = self::quartalRange($year, $quarter);

        $query = DB::table('qor_dpr')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.id', '=', 'qor_dpr.employee_id')
                     ->orOn('employees.nrp', '=', 'qor_dpr.nrp');
            })
            ->leftJoin('qor_pdty', function ($join) use ($year, $quarter) {
                $join->on('qor_pdty.nrp', '=', 'qor_dpr.nrp')
                     ->where('qor_pdty.tahun', '=', $year)
                     ->where('qor_pdty.quartal', '=', $quarter);
            })
            ->whereBetween('qor_dpr.tanggal', [$start, $end])
            ->selectRaw('
                qor_dpr.nrp,
                COALESCE(MAX(employees.name), MAX(qor_dpr.nama), qor_dpr.nrp) as nama,
                COALESCE(MAX(employees.jabatan_ss6), MAX(employees.jabatan_roster)) as jabatan,
                MAX(qor_dpr.kode_unit) as kode_unit,
                MAX(qor_dpr.loader) as loader,
                ROUND(AVG(qor_dpr.jarak), 1) as avg_jarak,
                SUM(qor_dpr.rit) as total_rit,
                SUM(qor_dpr.total_bcm) as total_bcm,
                SUM(qor_dpr.total_ton) as total_ton,
                MAX(qor_pdty.achievement_combined) as achievement_pct
            ')
            ->groupBy('qor_dpr.nrp');

        if ($kategori !== 'ALL') {
            $query->where('qor_dpr.source', $kategori);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('qor_dpr.nrp', 'like', "%{$search}%")
                  ->orWhere('qor_dpr.nama', 'like', "%{$search}%")
                  ->orWhere('employees.name', 'like', "%{$search}%")
                  ->orWhere('qor_dpr.kode_unit', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('total_bcm')->paginate($perPage);
    }

    public function getMonthlyReport(int $year, int $quarter, ?string $search = null, int $perPage = 50, ?int $glId = null)
    {
        [$start, $end] = self::quartalRange($year, $quarter);

        $query = DB::table('qor_dpr')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.id', '=', 'qor_dpr.employee_id')
                     ->orOn('employees.nrp', '=', 'qor_dpr.nrp');
            })
            ->whereBetween('qor_dpr.tanggal', [$start, $end]);

        if ($glId) {
            $query->where('employees.gl_id', $glId);
        }

        $query->selectRaw('
                qor_dpr.nrp,
                COALESCE(MAX(employees.name), MAX(qor_dpr.nama), qor_dpr.nrp) as nama,
                COALESCE(MAX(employees.jabatan_ss6), MAX(employees.jabatan_roster)) as jabatan,
                MONTH(qor_dpr.tanggal) as bulan,
                SUM(qor_dpr.rit) as total_rit,
                SUM(qor_dpr.total_bcm) as total_bcm,
                SUM(qor_dpr.total_ton) as total_ton,
                COUNT(DISTINCT qor_dpr.tanggal) as active_days
            ')
            ->groupBy('qor_dpr.nrp', DB::raw('MONTH(qor_dpr.tanggal)'));

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('qor_dpr.nrp', 'like', "%{$search}%")
                  ->orWhere('qor_dpr.nama', 'like', "%{$search}%")
                  ->orWhere('employees.name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('qor_dpr.nrp')->orderBy('bulan')->get();
    }

    public function getQuartalRanking(int $year, int $quarter, ?string $search = null, int $perPage = 50)
    {
        $query = MorRanking::query()
            ->period($year, $quarter);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nrp', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        $results = $query->orderBy('ranking')->paginate($perPage);

        // If no MOR ranking yet, fallback to QorPerforma or calculate
        if ($results->isEmpty()) {
            $bcmRank = QorPerforma::query()
                ->with('employee')
                ->period($year, $quarter)
                ->orderBy('ranking')
                ->paginate($perPage);

            return $bcmRank;
        }

        return $results;
    }

    public function getDprDaily(string $nrp, int $year, int $quarter): Collection
    {
        [$start, $end] = self::quartalRange($year, $quarter);

        return DB::table('qor_dpr')
            ->where('nrp', $nrp)
            ->whereBetween('tanggal', [$start, $end])
            ->select('tanggal', 'role_op', 'loader', 'kode_unit', 'jarak', 'rit', 'total_bcm', 'total_ton', 'source')
            ->orderBy('tanggal')
            ->get();
    }

    public function runRecalculation(int $year, int $quarter): array
    {
        // 1. Calculate BCM basic aggregates
        $this->calculateQuarterlyPerforma($year, $quarter);

        // 2. Calculate PDTY (OB + COAL)
        $pdtyRes = $this->pdtyService->calculateForPeriod($year, $quarter);

        // 3. Calculate MOR Ranking
        $morRes = $this->morService->calculateForPeriod($year, $quarter);

        return [
            'success' => true,
            'pdty' => $pdtyRes,
            'mor' => $morRes,
        ];
    }

    public function resetPeriod(int $year, int $quarter, bool $resetAll = false): void
    {
        if ($resetAll) {
            DB::table('qor_dpr')->truncate();
            DB::table('qor_sap_hm')->truncate();
            DB::table('qor_atr_attendance')->truncate();
            DB::table('qor_pdty')->truncate();
            DB::table('mor_ranking')->truncate();
            DB::table('qor_performa')->truncate();
            return;
        }

        [$start, $end] = self::quartalRange($year, $quarter);

        DB::table('qor_dpr')->whereBetween('tanggal', [$start, $end])->delete();
        DB::table('qor_sap_hm')->whereBetween('tanggal', [$start, $end])->delete();
        DB::table('qor_atr_attendance')->whereBetween('tanggal', [$start, $end])->delete();
        DB::table('qor_pdty')->where('tahun', $year)->where('quartal', $quarter)->delete();
        DB::table('mor_ranking')->where('tahun', $year)->where('quartal', $quarter)->delete();
        DB::table('qor_performa')->where('tahun', $year)->where('quartal', $quarter)->delete();
    }

    public function importFile(UploadedFile $file, string $type): array
    {
        return match ($type) {
            'dpr_ob' => $this->importService->importDpr($file, 'OB'),
            'dpr_coal' => $this->importService->importDpr($file, 'COAL'),
            'sap_hm' => $this->importService->importSapHm($file),
            'atr' => $this->importService->importAtr($file),
            default => $this->importService->importDpr($file, 'OB'),
        };
    }

    public function calculateQuarterlyPerforma(int $year, int $quarter): void
    {
        [$start, $end] = self::quartalRange($year, $quarter);

        $aggregates = DB::table('qor_dpr')
            ->selectRaw('
                employee_id,
                nrp,
                MAX(nama) as nama,
                MAX(loader) as loader,
                ROUND(AVG(jarak), 1) as jarak,
                MAX(kode_unit) as kode_unit,
                SUM(rit) as rit_total,
                SUM(total_bcm) as bcm_total
            ')
            ->whereBetween('tanggal', [$start, $end])
            ->groupBy('nrp', 'employee_id')
            ->get();

        foreach ($aggregates as $row) {
            QorPerforma::updateOrCreate(
                [
                    'employee_id' => $row->employee_id ?: 0,
                    'tahun' => $year,
                    'quartal' => $quarter
                ],
                [
                    'loader' => $row->loader,
                    'jarak' => $row->jarak,
                    'kode_unit' => $row->kode_unit,
                    'rit_total' => $row->rit_total,
                    'bcm_total' => $row->bcm_total,
                ]
            );
        }

        $this->refreshRanking($year, $quarter);
    }

    private function refreshRanking(int $year, int $quarter): void
    {
        $ranked = QorPerforma::query()
            ->period($year, $quarter)
            ->orderByDesc('bcm_total')
            ->orderByDesc('rit_total')
            ->pluck('id');

        foreach ($ranked as $index => $id) {
            QorPerforma::where('id', $id)->update([
                'ranking' => $index + 1,
                'dari_operator' => $ranked->count(),
            ]);
        }
    }
}
