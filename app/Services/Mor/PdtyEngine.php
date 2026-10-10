<?php

namespace App\Services\Mor;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Mesin PDTY (produktivitas COAL/OB), port dari V1
 * database/quartal_operator_report/qor_pdty_engine.php. Menghitung dari
 * qor_sap_hm (HM) + qor_dpr (produksi) + target_by_distance, lalu menulis
 * qor_pdty per (nrp, tahun, quartal, bulan, kategori); bulan 0 = agregat quartal.
 *
 * ACUAN FORMULA: sheet "PERFORMA (COAL)" / "PERFORMA (OB)" pada "MOR Agustus 2026.xlsx":
 *   COAL!AM = AL/AK, OB!AU = AT/AS, OB!AZ = (AM*AN + AU*AV)/(AV+AN).
 * achievement_pct / achievement_combined disimpan sebagai RASIO (0.96), sama seperti V1.
 *
 * Menggantikan QorPdtyService lama (port yang meniru varian Google Sheet dengan
 * salah ketik pola "HH57"/"539", target excavator berbeda, tanpa per-bulan).
 */
class PdtyEngine
{
    /** Kode Proses SAP untuk HM excavator OB (sheet LAIN2 kolom C di file MOR). */
    private const OB_EXCA_PROSES = ['P02', 'P03', 'P13', 'P14'];

    /** Kolom per kombinasi di qor_pdty. */
    private const COLUMNS = ['hd785_395', 'c777_395', 'hd785_1250', 'hd785_jumbo', 'c777_1250', 'hd785_2000', 'c777_2000', 'e1250', 'e6108', 'e395', 'e2000'];

    /** @var array<string, ?float> target_by_distance per (kategori|kombinasi|bucket) */
    private array $targetCache = [];

    public function __construct(private MorEngine $morEngine) {}

    /** Rentang satu periode: $bulan 1..12 = satu bulan, 0 = seluruh quartal. */
    public static function periodRange(int $tahun, int $quartal, int $bulan = 0): array
    {
        if ($bulan >= 1 && $bulan <= 12) {
            $start = sprintf('%04d-%02d-01', $tahun, $bulan);

            return [$start, date('Y-m-t', strtotime($start))];
        }
        $qDates = [1 => ['-01-01', '-03-31'], 2 => ['-04-01', '-06-30'], 3 => ['-07-01', '-09-30'], 4 => ['-10-01', '-12-31']];

        return [$tahun.($qDates[$quartal][0] ?? '-01-01'), $tahun.($qDates[$quartal][1] ?? '-12-31')];
    }

    public static function monthsOfQuarter(int $quartal): array
    {
        $quartal = max(1, min(4, $quartal));
        $first = ($quartal - 1) * 3 + 1;

        return [$first, $first + 1, $first + 2];
    }

    /**
     * Satu entri = satu kolom di sheet asli. is_excavator_hm = kategori "HM E____"
     * (operator excavator sendiri, filter langsung di kode_unit).
     */
    public static function categoryDefs(string $kategori): array
    {
        if ($kategori === 'COAL') {
            return [
                'hd785_395' => [
                    'is_excavator_hm' => false,
                    'sap_prefix_col' => 'kode_unit_2', 'sap_prefix_pattern' => '^(HD78|H57)',
                    'sap_class_pattern' => '^(E39|E539)', 'sap_jumbo_pattern' => null,
                    'dpr_unit_col' => 'kode_unit_2', 'dpr_unit_pattern' => '^(HD78|H57)', 'dpr_class_pattern' => '^(E39|E539)', 'dpr_jumbo' => false,
                    'target_kind' => 'distance', 'kombinasi' => '395', 'dist_bucket' => '395',
                ],
                'c777_395' => [
                    'is_excavator_hm' => false,
                    'sap_prefix_col' => 'kode_unit_2', 'sap_prefix_pattern' => '^(C577|C7)',
                    'sap_class_pattern' => '^(E39|E539)', 'sap_jumbo_pattern' => null,
                    'dpr_unit_col' => 'kode_unit_2', 'dpr_unit_pattern' => '^(C7|C577)', 'dpr_class_pattern' => '^(E39|E539)', 'dpr_jumbo' => false,
                    'target_kind' => 'distance', 'kombinasi' => '395', 'dist_bucket' => '395',
                ],
                'hd785_1250' => [
                    'is_excavator_hm' => false,
                    'sap_prefix_col' => 'kode_unit_2', 'sap_prefix_pattern' => '^(HD78|H57)',
                    'sap_class_pattern' => '^(E12|E61|E512)', 'sap_jumbo_pattern' => null,
                    'dpr_unit_col' => 'kode_unit_2', 'dpr_unit_pattern' => '^(HD78|H57)', 'dpr_class_pattern' => '^(E12|E61|E512)', 'dpr_jumbo' => false,
                    'target_kind' => 'distance', 'kombinasi' => '1250_HD785', 'dist_bucket' => '1250',
                ],
                'hd785_jumbo' => [
                    'is_excavator_hm' => false,
                    // Prefix truk dicek di kode_unit karena kode_unit_2 dipakai untuk flag JUMBO.
                    'sap_prefix_col' => 'kode_unit', 'sap_prefix_pattern' => '^(HD78|H57)',
                    'sap_class_pattern' => '^(E39|E539|E12|E61|E512|E20)', 'sap_jumbo_pattern' => '^(JUMBO)',
                    'dpr_unit_pattern' => '^(HD78|H57)', 'dpr_class_pattern' => '^(E39|E539|E12|E61|E512|E20)', 'dpr_jumbo' => true,
                    'target_kind' => 'distance', 'kombinasi' => '1250_JUMBO', 'dist_bucket' => '1250',
                ],
                'c777_1250' => [
                    'is_excavator_hm' => false,
                    // "C57" memang dipakai MOR!'PERFORMA (COAL)'!I untuk SAP[Kode Unit 2].
                    'sap_prefix_col' => 'kode_unit_2', 'sap_prefix_pattern' => '^(C7|C57)',
                    'sap_class_pattern' => '^(E12|E61|E512)', 'sap_jumbo_pattern' => null,
                    'dpr_unit_col' => 'kode_unit_2', 'dpr_unit_pattern' => '^(C7|C577)', 'dpr_class_pattern' => '^(E12|E61|E512)', 'dpr_jumbo' => false,
                    'target_kind' => 'distance', 'kombinasi' => '1250_C777', 'dist_bucket' => '1250',
                ],
                'e1250' => [
                    'is_excavator_hm' => true, 'sap_class_col' => 'kode_unit', 'sap_class_pattern' => '^(E12|E512)',
                    'dpr_class_pattern' => '^(E12|E512)', 'target_kind' => 'fixed', 'target_fixed' => 400,
                ],
                'e6108' => [
                    'is_excavator_hm' => true, 'sap_class_col' => 'kode_unit', 'sap_class_pattern' => '^(E61)',
                    'dpr_class_pattern' => '^(E61)', 'target_kind' => 'fixed', 'target_fixed' => 400,
                ],
                'e395' => [
                    'is_excavator_hm' => true, 'sap_class_col' => 'kode_unit', 'sap_class_pattern' => '^(E39|E539)',
                    'dpr_class_pattern' => '^(E39|E539)', 'target_kind' => 'fixed', 'target_fixed' => 350,
                ],
            ];
        }

        return [
            'hd785_395' => [
                'is_excavator_hm' => false,
                'sap_prefix_col' => 'kode_unit', 'sap_prefix_pattern' => '^(HD78|H57)',
                'sap_class_pattern' => '^(E39|E539)', 'sap_jumbo_pattern' => null,
                'dpr_unit_pattern' => '^(HD78|H57)', 'dpr_class_pattern' => '^(E39|E539)', 'dpr_jumbo' => false,
                'target_kind' => 'distance', 'kombinasi' => '395', 'dist_bucket' => '395',
            ],
            'c777_395' => [
                'is_excavator_hm' => false,
                'sap_prefix_col' => 'kode_unit', 'sap_prefix_pattern' => '^(C577|C7)',
                'sap_class_pattern' => '^(E39|E539)', 'sap_jumbo_pattern' => null,
                'dpr_unit_pattern' => '^(C7|C577)', 'dpr_class_pattern' => '^(E39|E539)', 'dpr_jumbo' => false,
                'target_kind' => 'distance', 'kombinasi' => '395', 'dist_bucket' => '395',
            ],
            'hd785_1250' => [
                'is_excavator_hm' => false,
                'sap_prefix_col' => 'kode_unit', 'sap_prefix_pattern' => '^(HD78|H57)',
                'sap_class_pattern' => '^(E12|E61|E512)', 'sap_jumbo_pattern' => null,
                'dpr_unit_pattern' => '^(HD78|H57)', 'dpr_class_pattern' => '^(E12|E61|E512)', 'dpr_jumbo' => false,
                'target_kind' => 'distance', 'kombinasi' => '1250', 'dist_bucket' => '1250',
            ],
            'c777_1250' => [
                'is_excavator_hm' => false,
                'sap_prefix_col' => 'kode_unit', 'sap_prefix_pattern' => '^(C577|C7)',
                'sap_class_pattern' => '^(E12|E61|E512)', 'sap_jumbo_pattern' => null,
                'dpr_unit_pattern' => '^(C7|C577)', 'dpr_class_pattern' => '^(E12|E61|E512)', 'dpr_jumbo' => false,
                'target_kind' => 'distance', 'kombinasi' => '1250', 'dist_bucket' => '1250',
            ],
            'hd785_2000' => [
                'is_excavator_hm' => false,
                'sap_prefix_col' => 'kode_unit', 'sap_prefix_pattern' => '^(HD78|H57)',
                'sap_class_pattern' => '^(E20)', 'sap_jumbo_pattern' => null,
                'dpr_unit_pattern' => '^(HD78|H57)', 'dpr_class_pattern' => '^(E20)', 'dpr_jumbo' => false,
                'target_kind' => 'distance', 'kombinasi' => '2000', 'dist_bucket' => '2000',
            ],
            'c777_2000' => [
                'is_excavator_hm' => false,
                'sap_prefix_col' => 'kode_unit', 'sap_prefix_pattern' => '^(C7|C577)',
                'sap_class_pattern' => '^(E20)', 'sap_jumbo_pattern' => null,
                'dpr_unit_pattern' => '^(C7|C577)', 'dpr_class_pattern' => '^(E20)', 'dpr_jumbo' => false,
                'target_kind' => 'distance', 'kombinasi' => '2000', 'dist_bucket' => '2000',
            ],
            'e2000' => [
                'is_excavator_hm' => true, 'sap_class_col' => 'kode_unit', 'sap_class_pattern' => '^(E20)',
                'sap_act_values_override' => self::OB_EXCA_PROSES,
                'dpr_class_pattern' => '^(E20)', 'target_kind' => 'fixed', 'target_fixed' => 750,
            ],
            'e1250' => [
                'is_excavator_hm' => true, 'sap_class_col' => 'kode_unit', 'sap_class_pattern' => '^(E12|E512)',
                'sap_act_values_override' => self::OB_EXCA_PROSES,
                'dpr_class_pattern' => '^(E12|E512)', 'target_kind' => 'fixed', 'target_fixed' => 480,
            ],
            'e6108' => [
                'is_excavator_hm' => true, 'sap_class_col' => 'kode_unit', 'sap_class_pattern' => '^(E61)',
                'sap_act_values_override' => self::OB_EXCA_PROSES,
                'dpr_class_pattern' => '^(E61)', 'target_kind' => 'fixed', 'target_fixed' => 480,
            ],
            'e395' => [
                'is_excavator_hm' => true, 'sap_class_col' => 'kode_unit', 'sap_class_pattern' => '^(E39|E539)',
                'sap_act_values_override' => self::OB_EXCA_PROSES,
                'dpr_class_pattern' => '^(E39|E539)', 'target_kind' => 'fixed', 'target_fixed' => 400,
            ],
        ];
    }

    /** Urutan bobot SUMPRODUCT persis formula AK/AL (COAL) & AS/AT (OB). */
    public static function weightOrder(string $kategori): array
    {
        return $kategori === 'COAL'
            ? ['hd785_395', 'c777_395', 'hd785_1250', 'hd785_jumbo', 'c777_1250', 'e1250', 'e6108', 'e395']
            : ['hd785_395', 'c777_395', 'hd785_1250', 'c777_1250', 'hd785_2000', 'c777_2000', 'e2000', 'e1250', 'e6108', 'e395'];
    }

    /** Loader-prefix per bucket jarak (sama untuk COAL & OB). */
    public static function distBucketDefs(): array
    {
        return ['395' => '^(E39|E539)', '1250' => '^(E12|E512|E61)', '2000' => '^(E20)'];
    }

    // ── Query ───────────────────────────────────────────────────────────────

    /** Syarat baris SAP untuk satu kombinasi (sama persis dengan WHERE per kombinasi di V1). */
    private static function sapCondition(string $kategori, array $def): array
    {
        if ($kategori === 'COAL') {
            $actValues = ['A02', 'A03'];
            $actCol = 'aktivitas';
        } else {
            $actValues = $def['sap_act_values_override'] ?? ['P02', 'P03'];
            $actCol = 'proses';
        }
        $sql = "$actCol IN (".implode(',', array_fill(0, count($actValues), '?')).')';
        $params = $actValues;

        if (! empty($def['is_excavator_hm'])) {
            $sql .= " AND {$def['sap_class_col']} REGEXP ?";
            $params[] = $def['sap_class_pattern'];
        } else {
            $sql .= " AND {$def['sap_prefix_col']} REGEXP ? AND alat_muat REGEXP ?";
            $params[] = $def['sap_prefix_pattern'];
            $params[] = $def['sap_class_pattern'];
            if (! empty($def['sap_jumbo_pattern'])) {
                $sql .= ' AND kode_unit_2 REGEXP ?';
                $params[] = $def['sap_jumbo_pattern'];
            }
        }

        return [$sql, $params];
    }

    /** Syarat baris DPR untuk satu kombinasi (sama persis dengan WHERE per kombinasi di V1). */
    private static function dprCondition(array $def): array
    {
        $sql = 'role_op = ? AND loader REGEXP ?';
        $params = [$def['is_excavator_hm'] ? 'EXCA' : 'HD', $def['dpr_class_pattern']];

        if (! $def['is_excavator_hm']) {
            // COAL truk non-jumbo -> KODE UNIT 2; COAL jumbo -> KODE UNIT + KODE UNIT 2 = JUMBO;
            // OB semua -> KODE UNIT. Tanpa ini unit jumbo terhitung dobel.
            $unitCol = $def['dpr_unit_col'] ?? 'kode_unit';
            $sql .= " AND $unitCol REGEXP ?";
            $params[] = $def['dpr_unit_pattern'];
            if (! empty($def['dpr_jumbo'])) {
                $sql .= " AND kode_unit_2 REGEXP '^(JUMBO)'";
            }
        }

        return [$sql, $params];
    }

    /**
     * SUM HM SAP semua kombinasi dalam satu pemindaian: SUM(CASE WHEN syarat THEN jam END)
     * per kombinasi. Syarat & penjumlahan DECIMAL tetap di MySQL, jadi angkanya sama
     * dengan satu query per kombinasi.
     *
     * @return array<string, array<string, float>> kombinasi => [nrp => jam]
     */
    public function sumSapHm(string $start, string $end, string $kategori, array $defs): array
    {
        $col = $kategori === 'COAL' ? 'hour_actv' : 'hour_time';
        $conds = array_map(fn ($def) => self::sapCondition($kategori, $def), $defs);

        return $this->conditionalSums('qor_sap_hm', $col, $conds, 'tanggal BETWEEN ? AND ?', [$start, $end],
            ['kode_unit', 'kode_unit_2', 'alat_muat', 'aktivitas', 'proses']);
    }

    /** @return array<string, array<string, float>> kombinasi => [nrp => produksi (ton/BCM)] */
    public function sumDprActual(string $start, string $end, string $source, array $defs): array
    {
        $col = $source === 'COAL' ? 'total_ton' : 'total_bcm';
        $conds = array_map(fn ($def) => self::dprCondition($def), $defs);

        return $this->conditionalSums('qor_dpr', $col, $conds, 'source = ? AND tanggal BETWEEN ? AND ?', [$source, $start, $end],
            ['role_op', 'loader', 'kode_unit', 'kode_unit_2']);
    }

    /**
     * Satu query: kolom per kombinasi = COALESCE(SUM(CASE WHEN syarat THEN col END), 0), GROUP BY nrp.
     * Baris lebih dulu dijumlahkan per kombinasi unik kolom yang dipakai syarat ($groupCols),
     * supaya REGEXP hanya dievaluasi pada kombinasi unik, bukan tiap baris. SUM bertingkat
     * DECIMAL tetap eksak dan REGEXP tidak peka huruf besar (collation ci), jadi hasilnya sama
     * dengan satu query per kombinasi. NRP tanpa baris cocok bernilai 0 (= COALESCE(SUM, 0)).
     */
    private function conditionalSums(string $table, string $col, array $conds, string $where, array $whereParams, array $groupCols): array
    {
        $selects = [];
        $params = [];
        $alias = [];
        foreach (array_keys($conds) as $i => $key) {
            [$condSql, $condParams] = $conds[$key];
            $selects[] = "COALESCE(SUM(CASE WHEN $condSql THEN s END), 0) AS c$i";
            array_push($params, ...$condParams);
            $alias["c$i"] = $key;
        }
        $group = implode(', ', $groupCols);
        $rows = DB::select('SELECT nrp, '.implode(', ', $selects)
            ." FROM (SELECT nrp, $group, SUM($col) AS s FROM $table WHERE $where GROUP BY nrp, $group) t GROUP BY nrp",
            array_merge($params, $whereParams));

        $out = array_fill_keys(array_keys($conds), []);
        foreach ($rows as $row) {
            $k = self::nrpKey($row->nrp);
            foreach ($alias as $c => $key) {
                $out[$key][$k] = (float) $row->$c;
            }
        }

        return $out;
    }

    /**
     * SUM(distance)/SUM(metric) (role HD + loader prefix), ROUND(...,-2), per NRP.
     * NRP tanpa data tidak ada di hasil (= null), supaya target yang bergantung
     * padanya ditandai unresolved.
     *
     * @return array<string, ?float>
     */
    public function distanceBucket(string $start, string $end, string $source, string $loaderPattern): array
    {
        $metricCol = $source === 'COAL' ? 'total_ton' : 'total_bcm';
        $rows = DB::select("SELECT nrp, SUM(distance) AS d, SUM($metricCol) AS m FROM qor_dpr
            WHERE source = ? AND role_op = 'HD' AND tanggal BETWEEN ? AND ? AND loader REGEXP ?
            GROUP BY nrp", [$source, $start, $end, $loaderPattern]);
        $out = [];
        foreach ($rows as $row) {
            $out[self::nrpKey($row->nrp)] = ($row->m === null || (float) $row->m == 0.0)
                ? null
                : round(((float) $row->d / (float) $row->m) / 100) * 100;
        }

        return $out;
    }

    /** MATCH(key, range, 1): jarak_min terbesar <= bucket; null bila target belum ada. */
    public function targetLookup(string $kategori, string $kombinasi, ?float $distanceBucket): ?float
    {
        if ($distanceBucket === null) {
            return null;
        }
        $cacheKey = "$kategori|$kombinasi|$distanceBucket";
        if (array_key_exists($cacheKey, $this->targetCache)) {
            return $this->targetCache[$cacheKey];
        }
        $v = DB::table('target_by_distance')
            ->where('kategori', $kategori)->where('kombinasi', $kombinasi)->where('jarak_min', '<=', $distanceBucket)
            ->orderByDesc('jarak_min')->value('target_pdty');

        return $this->targetCache[$cacheKey] = $v === null ? null : (float) $v;
    }

    /** Kesetaraan NRP mengikuti collation MySQL (tidak peka huruf besar & spasi di belakang). */
    private static function nrpKey(string $nrp): string
    {
        return strtoupper(rtrim($nrp));
    }

    // ── Hitung & simpan ─────────────────────────────────────────────────────

    /**
     * Hitung PDTY semua NRP untuk 1 periode + 1 kategori. Data SAP/DPR diambil
     * sekali untuk seluruh NRP & kombinasi; logika per NRP sama dengan V1.
     *
     * @return array<string, array> nrp => hasil
     */
    public function computeForNrps(array $nrps, int $tahun, int $quartal, string $kategori, int $bulan = 0): array
    {
        [$start, $end] = self::periodRange($tahun, $quartal, $bulan);
        $defs = self::categoryDefs($kategori);

        $distByBucket = [];
        foreach (self::distBucketDefs() as $bucketKey => $loaderPattern) {
            $distByBucket[$bucketKey] = $this->distanceBucket($start, $end, $kategori, $loaderPattern);
        }
        $hmByDef = $this->sumSapHm($start, $end, $kategori, $defs);
        $actByDef = $this->sumDprActual($start, $end, $kategori, $defs);

        $results = [];
        foreach ($nrps as $nrp) {
            $k = self::nrpKey($nrp);
            $distBuckets = [];
            foreach (array_keys($distByBucket) as $bucketKey) {
                $distBuckets[$bucketKey] = $distByBucket[$bucketKey][$k] ?? null;
            }
            $results[$nrp] = $this->computeRow($nrp, $tahun, $quartal, $kategori, $bulan, $defs, $distBuckets,
                array_map(fn ($m) => $m[$k] ?? 0.0, $hmByDef), array_map(fn ($m) => $m[$k] ?? 0.0, $actByDef));
        }

        return $results;
    }

    /** Hitung PDTY 1 nrp untuk 1 periode + 1 kategori. */
    public function computeForNrp(string $nrp, int $tahun, int $quartal, string $kategori, int $bulan = 0): array
    {
        return $this->computeForNrps([$nrp], $tahun, $quartal, $kategori, $bulan)[$nrp];
    }

    /** Rumus PERFORMA (COAL/OB) satu NRP dari HM, produksi, dan bucket jarak yang sudah dijumlah. */
    private function computeRow(string $nrp, int $tahun, int $quartal, string $kategori, int $bulan, array $defs, array $distBuckets, array $hmVals, array $actVals): array
    {
        $hm = $act = $pdty = $target = [];
        $unresolved = [];

        foreach ($defs as $key => $def) {
            $hmVal = $hmVals[$key];
            $actVal = $actVals[$key];
            $hm[$key] = $hmVal;
            $act[$key] = $actVal;
            $pdty[$key] = $hmVal > 0 ? ($actVal / $hmVal) : 0.0; // IFERROR(act/hm, 0)

            if ($def['target_kind'] === 'fixed') {
                $target[$key] = $hmVal != 0 ? (float) $def['target_fixed'] : 0.0;
            } elseif ($hmVal == 0 || $act[$key] == 0) {
                // IF(actual_ratio = 0; 0; INDEX/MATCH(...)) di sheet asli.
                $target[$key] = 0.0;
            } else {
                $t = $this->targetLookup($kategori, $def['kombinasi'], $distBuckets[$def['dist_bucket']] ?? null);
                if ($t === null) {
                    $target[$key] = null;
                    $unresolved[] = $key;
                } else {
                    $target[$key] = $t;
                }
            }
        }

        $sumTargetNum = 0.0;
        $sumActNum = 0.0;
        $sumWeight = 0.0;
        foreach (self::weightOrder($kategori) as $key) {
            $w = $hm[$key];
            if ($w == 0) {
                continue; // term w*x = 0, seperti SUMPRODUCT asli
            }
            $sumWeight += $w;
            $sumActNum += $pdty[$key] * $w;
            if ($target[$key] !== null) {
                $sumTargetNum += $target[$key] * $w;
            }
        }

        $sumproductTarget = $sumWeight > 0 ? ($sumTargetNum / $sumWeight) : 0.0;
        $sumproductAct = $sumWeight > 0 ? ($sumActNum / $sumWeight) : 0.0;
        // Target belum lengkap -> jangan laporkan angka yang salah.
        $achievement = $unresolved ? null : ($sumproductTarget == 0 ? 0.0 : $sumproductAct / $sumproductTarget);

        return [
            'nrp' => $nrp, 'tahun' => $tahun, 'quartal' => $quartal, 'bulan' => $bulan, 'kategori' => $kategori,
            'hm' => $hm, 'act' => $act, 'pdty' => $pdty, 'target' => $target,
            'dist_buckets' => $distBuckets,
            'hm_total' => array_sum($hm),
            'sumproduct_target' => $sumproductTarget,
            'sumproduct_act' => $sumproductAct,
            'achievement_pct' => $achievement,
            'target_unresolved' => $unresolved,
        ];
    }

    /** Baris qor_pdty dari satu hasil hitung (kolom kunci + nilai). */
    private function resultRow(?string $nama, array $result): array
    {
        $values = [
            'nrp' => $result['nrp'], 'tahun' => $result['tahun'], 'quartal' => $result['quartal'],
            'bulan' => $result['bulan'], 'kategori' => $result['kategori'],
            'nama' => $nama,
            'hm_total' => $result['hm_total'],
            'sumproduct_target' => $result['sumproduct_target'],
            'sumproduct_act' => $result['sumproduct_act'],
            'achievement_pct' => $result['achievement_pct'],
            'target_unresolved' => $result['target_unresolved'] ? implode(',', $result['target_unresolved']) : null,
            'updated_at' => now(),
        ];
        foreach (self::COLUMNS as $key) {
            foreach (['hm' => 'hm_', 'act' => 'act_', 'pdty' => 'pdty_', 'target' => 'target_'] as $arr => $prefix) {
                $values[$prefix.$key] = $result[$arr][$key] ?? null;
            }
        }
        foreach (['395', '1250', '2000'] as $b) {
            $values["dist_bucket_$b"] = $result['dist_buckets'][$b] ?? null;
        }

        return $values;
    }

    /** Simpan hasil (upsert per batch); achievement_combined diisi terpisah oleh refreshCombined(). */
    private function saveRows(array $rows): void
    {
        if (! $rows) {
            return;
        }
        $keys = ['nrp', 'tahun', 'quartal', 'bulan', 'kategori'];
        $update = array_values(array_diff(array_keys($rows[0]), $keys));
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('qor_pdty')->upsert($chunk, $keys, $update);
        }
    }

    /**
     * ACH PDTY COAL OB: rata-rata tertimbang HM achievement COAL & OB, disimpan di
     * baris OB. Dihitung dari nilai yang SUDAH tersimpan (DECIMAL), sama seperti V1.
     * Null bila salah satu belum ada/unresolved.
     */
    public function refreshCombined(array $nrps, int $tahun, int $quartal, int $bulan = 0): void
    {
        foreach (array_chunk($nrps, 500) as $chunk) {
            $byNrp = [];
            DB::table('qor_pdty')
                ->where(['tahun' => $tahun, 'quartal' => $quartal, 'bulan' => $bulan])
                ->whereIn('kategori', ['COAL', 'OB'])
                ->whereIn('nrp', $chunk)
                ->get(['nrp', 'kategori', 'hm_total', 'achievement_pct'])
                ->each(function ($r) use (&$byNrp) {
                    $byNrp[self::nrpKey($r->nrp)][$r->kategori] = $r;
                });

            DB::transaction(function () use ($chunk, $byNrp, $tahun, $quartal, $bulan) {
                foreach ($chunk as $nrp) {
                    $coal = $byNrp[self::nrpKey($nrp)]['COAL'] ?? null;
                    $ob = $byNrp[self::nrpKey($nrp)]['OB'] ?? null;
                    if (! $coal || ! $ob) {
                        continue;
                    }
                    $combined = null;
                    if ($coal->achievement_pct !== null && $ob->achievement_pct !== null) {
                        $denom = (float) $coal->hm_total + (float) $ob->hm_total;
                        $combined = $denom == 0 ? 0.0
                            : (((float) $coal->achievement_pct * (float) $coal->hm_total) + ((float) $ob->achievement_pct * (float) $ob->hm_total)) / $denom;
                    }
                    DB::table('qor_pdty')
                        ->where(['nrp' => $nrp, 'tahun' => $tahun, 'quartal' => $quartal, 'bulan' => $bulan, 'kategori' => 'OB'])
                        ->update(['achievement_combined' => $combined]);
                }
            });
        }
    }

    /** Nama karyawan per NRP (baris pertama bila NRP ganda, seperti LIMIT 1 di V1). */
    private function namaMap(array $nrps): array
    {
        $map = [];
        try {
            foreach (array_chunk($nrps, 500) as $chunk) {
                foreach (DB::table('employees')->whereIn('nrp', $chunk)->orderBy('id')->get(['nrp', 'name']) as $e) {
                    $map[self::nrpKey($e->nrp)] ??= $e->name;
                }
            }
        } catch (Throwable) {
        }

        return $map;
    }

    /** Hitung & simpan PDTY COAL + OB untuk sekumpulan NRP pada satu periode. */
    public function recalcPeriodForNrps(int $tahun, int $quartal, array $nrps, int $bulan = 0): int
    {
        $nrps = array_values(array_filter(array_unique($nrps), fn ($n) => $n !== '' && $n !== null));
        if (! $nrps) {
            return 0;
        }
        $nama = $this->namaMap($nrps);
        foreach (['COAL', 'OB'] as $kategori) {
            $rows = [];
            foreach ($this->computeForNrps($nrps, $tahun, $quartal, $kategori, $bulan) as $nrp => $result) {
                $rows[] = $this->resultRow($nama[self::nrpKey($nrp)] ?? null, $result);
            }
            $this->saveRows($rows);
        }
        $this->refreshCombined($nrps, $tahun, $quartal, $bulan);

        return count($nrps);
    }

    /** NRP yang punya data SAP/DPR di rentang tanggal. */
    public function nrpsWithData(string $start, string $end): array
    {
        return array_map(fn ($r) => $r->nrp, DB::select("SELECT DISTINCT nrp FROM qor_sap_hm WHERE tanggal BETWEEN ? AND ? AND nrp NOT IN ('', '0')
            UNION SELECT DISTINCT nrp FROM qor_dpr WHERE tanggal BETWEEN ? AND ? AND nrp NOT IN ('', '0')", [$start, $end, $start, $end]));
    }

    /**
     * Hitung ulang satu quartal (aksi "Hitung Ulang PDTY" V1): agregat quartal untuk
     * semua NRP berdata, lalu per bulan yang punya data; bulan-bulannya ditandai
     * supaya snapshot Raport MOR ikut dihitung ulang.
     *
     * @return array{operator: int, bulan: array<int>}
     */
    public function recalcQuarter(int $tahun, int $quartal): array
    {
        [$start, $end] = self::periodRange($tahun, $quartal);
        $count = $this->recalcPeriodForNrps($tahun, $quartal, $this->nrpsWithData($start, $end));

        $bulanDihitung = [];
        foreach (self::monthsOfQuarter($quartal) as $m) {
            $nrps = $this->nrpsWithData(...self::periodRange($tahun, $quartal, $m));
            if (! $nrps) {
                continue;
            }
            $this->recalcPeriodForNrps($tahun, $quartal, $nrps, $m);
            $bulanDihitung[] = $m;
        }

        $this->morEngine->markSnapshotDirty(MorEngine::quarterMonths($tahun, $quartal));

        return ['operator' => $count, 'bulan' => $bulanDihitung];
    }
}
