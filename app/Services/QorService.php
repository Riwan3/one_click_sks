<?php

namespace App\Services;

use App\Models\QorDpr;
use App\Models\QorPdty;
use App\Models\QorPerforma;
use App\Services\Mor\MorEngine;
use App\Services\Mor\MorInputBuilder;
use App\Services\Mor\PdtyEngine;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QorService
{
    public function __construct(
        private PdtyEngine $pdtyEngine,
        private MorEngine $morEngine,
        private MorInputBuilder $morInputBuilder,
        private QorImportService $importService
    ) {}

    public static function quartalRange(int $year, int $quarter): array
    {
        return PdtyEngine::periodRange($year, $quarter);
    }

    public function getOperatorReport(int $year, int $quarter, ?string $search = null, string $kategori = 'ALL', int $perPage = 50)
    {
        [$start, $end] = self::quartalRange($year, $quarter);

        $query = DB::table('qor_dpr')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.id', '=', 'qor_dpr.employee_id')
                     ->orOn('employees.nrp', '=', 'qor_dpr.nrp');
            })
            // Hanya baris agregat quartal kategori OB (tempat achievement COAL+OB disimpan);
            // join ke semua baris (COAL, OB, per bulan) menggandakan SUM rit/BCM/ton.
            ->leftJoin('qor_pdty', function ($join) use ($year, $quarter) {
                $join->on('qor_pdty.nrp', '=', 'qor_dpr.nrp')
                     ->where('qor_pdty.tahun', '=', $year)
                     ->where('qor_pdty.quartal', '=', $quarter)
                     ->where('qor_pdty.bulan', '=', 0)
                     ->where('qor_pdty.kategori', '=', 'OB');
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
                MAX(qor_pdty.achievement_combined) * 100 as achievement_pct
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

    /**
     * Ranking MOR quartal dari snapshot mesin MOR (mor_raport_bulanan periode Q),
     * ranking per sheet jabatan seperti file MOR. Dipetakan ke kolom tabel Quartal Report.
     */
    public function getQuartalRanking(int $year, int $quarter, ?string $search = null, int $perPage = 50)
    {
        $kategoriLabel = ['IS' => 'Istimewa', 'BS' => 'Baik Sekali'];
        $query = DB::table('mor_raport_bulanan')
            ->where(['tahun' => $year, 'periode' => 'Q', 'bulan' => $quarter]);

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nrp', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%")
                  ->orWhere('sheet', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('sheet')->orderBy('ranking')->paginate($perPage)
            ->through(function ($r) use ($kategoriLabel) {
                $detail = json_decode($r->detail, true) ?: [];
                $poin = $detail['poin'] ?? [];
                $produksi = $detail['produksi'] ?? [];
                $row = [
                    'id' => $r->id,
                    'nrp' => $r->nrp,
                    'nama' => $r->nama,
                    'ranking' => (int) $r->ranking,
                    'jumlah' => (int) $r->jumlah,
                    'kategori' => $kategoriLabel[$r->kategori] ?? ($r->prasyarat === 'OK' ? null : 'Belum lolos prasyarat'),
                    'jabatan' => $r->jabatan,
                    'grup' => $r->sheet,
                    'total_poin' => (float) $r->total_poin,
                    'atr_persen' => $produksi['atr_persen'] ?? null,
                    'ts_persen' => isset($produksi['timesheet_persen']) ? round((float) $produksi['timesheet_persen'], 1) : null,
                    'hm_persen' => $produksi['hm_ach'] ?? null,
                    'pdty_persen' => $produksi['pdty_combined'] ?? null,
                ];
                // Komponen yang tidak dinilai di sheet ini tidak dikirim (tabel menampilkan "-").
                foreach (['atr_point' => 'POIN ATR', 'ts_point' => 'POIN TS', 'hm_point' => 'POIN HM', 'pdty_point' => 'POIN PDTY'] as $col => $key) {
                    if (array_key_exists($key, $poin)) {
                        $row[$col] = (float) $poin[$key];
                    }
                }

                return $row;
            });
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

    /**
     * Hitung ulang satu quartal: agregat BCM, PDTY (mesin V1, quartal + per bulan),
     * lalu snapshot Raport MOR bulan/quartal/tahun yang terdampak (sekitar 15-20 detik).
     */
    public function runRecalculation(int $year, int $quarter): array
    {
        $this->calculateQuarterlyPerforma($year, $quarter);
        $pdty = $this->pdtyEngine->recalcQuarter($year, $quarter);
        $periodeRaport = $this->morInputBuilder->refreshDirty();

        return [
            'success' => true,
            'pdty' => $pdty,
            'periode_raport' => $periodeRaport,
        ];
    }

    /** Tandai bulan-bulan yang datanya berubah supaya Raport MOR ikut dihitung ulang. */
    private function markDirtyBetween(string $start, string $end): void
    {
        $months = [];
        for ($d = strtotime(date('Y-m-01', strtotime($start))); $d <= strtotime($end); $d = strtotime('+1 month', $d)) {
            $months[] = date('Y-m', $d);
        }
        $this->morEngine->markSnapshotDirty($months);
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
            DB::table('mor_raport_bulanan')->truncate();
            return;
        }

        [$start, $end] = self::quartalRange($year, $quarter);

        DB::table('qor_dpr')->whereBetween('tanggal', [$start, $end])->delete();
        DB::table('qor_sap_hm')->whereBetween('tanggal', [$start, $end])->delete();
        DB::table('qor_atr_attendance')->whereBetween('tanggal', [$start, $end])->delete();
        DB::table('qor_pdty')->where('tahun', $year)->where('quartal', $quarter)->delete();
        DB::table('mor_ranking')->where('tahun', $year)->where('quartal', $quarter)->delete();
        DB::table('qor_performa')->where('tahun', $year)->where('quartal', $quarter)->delete();

        // Raport bulan & quartal ini tidak lagi punya data; raport tahunan dihitung ulang.
        DB::table('mor_raport_bulanan')->where('tahun', $year)->where(function ($q) use ($quarter) {
            $q->where(fn ($m) => $m->where('periode', 'M')->whereIn('bulan', PdtyEngine::monthsOfQuarter($quarter)))
              ->orWhere(fn ($m) => $m->where('periode', 'Q')->where('bulan', $quarter));
        })->delete();
        $this->markDirtyBetween($start, $end);
    }

    /** Import satu file lalu tandai bulan yang datanya berubah untuk dihitung ulang di Raport MOR. */
    public function importFile(UploadedFile $file, string $type): array
    {
        $table = match ($type) {
            'sap_hm' => 'qor_sap_hm',
            'atr' => 'qor_atr_attendance',
            default => 'qor_dpr',
        };
        $mulai = now()->subSecond();

        $result = match ($type) {
            'dpr_ob' => $this->importService->importDpr($file, 'OB'),
            'dpr_coal' => $this->importService->importDpr($file, 'COAL'),
            'sap_hm' => $this->importService->importSapHm($file),
            'atr' => $this->importService->importAtr($file),
            default => $this->importService->importDpr($file, 'OB'),
        };

        $range = DB::table($table)
            ->where(fn ($q) => $q->where('created_at', '>=', $mulai)->orWhere('updated_at', '>=', $mulai))
            ->selectRaw('MIN(tanggal) mn, MAX(tanggal) mx')->first();
        if ($range?->mn) {
            $this->markDirtyBetween($range->mn, $range->mx);
        }

        return $result;
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
                COALESCE(SUM(rit), 0) as rit_total,
                COALESCE(SUM(total_bcm), 0) as bcm_total
            ')
            ->whereBetween('tanggal', [$start, $end])
            // qor_performa disusun per karyawan; NRP DPR yang belum ada di employees
            // dilewati di sini (PDTY & Raport MOR tetap menghitungnya per NRP).
            ->whereNotNull('employee_id')
            ->groupBy('nrp', 'employee_id')
            ->get();

        foreach ($aggregates as $row) {
            QorPerforma::updateOrCreate(
                [
                    'employee_id' => $row->employee_id,
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
