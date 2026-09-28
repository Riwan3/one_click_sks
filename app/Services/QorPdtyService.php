<?php

namespace App\Services;

use App\Models\QorPdty;
use Illuminate\Support\Facades\DB;

class QorPdtyService
{
    public const QUARTAL_MONTHS = [
        1 => ['-01-01', '-03-31'],
        2 => ['-04-01', '-06-30'],
        3 => ['-07-01', '-09-30'],
        4 => ['-10-01', '-12-31'],
    ];

    public static function quarterRange(int $tahun, int $quartal): array
    {
        $dates = self::QUARTAL_MONTHS[$quartal] ?? self::QUARTAL_MONTHS[1];
        return [
            $tahun . $dates[0],
            $tahun . $dates[1],
        ];
    }

    public static function categoryDefs(string $kategori): array
    {
        if ($kategori === 'COAL') {
            return [
                'hd785_395' => [
                    'label' => 'HD785 VS 395', 'is_excavator_hm' => false,
                    'sap_prefix_col' => 'kode_unit_2', 'sap_prefix_pattern' => '^(HD78|H57)',
                    'sap_class_pattern' => '^(E39|E539)', 'sap_jumbo_pattern' => null,
                    'dpr_unit_pattern' => '^(HD78|H57)', 'dpr_class_pattern' => '^(E39|E539)', 'dpr_jumbo' => false,
                    'target_kind' => 'distance', 'kombinasi' => '395', 'dist_bucket' => '395',
                ],
                'c777_395' => [
                    'label' => 'C777 VS 395', 'is_excavator_hm' => false,
                    'sap_prefix_col' => 'kode_unit_2', 'sap_prefix_pattern' => '^(C577|C7)',
                    'sap_class_pattern' => '^(E39|E539)', 'sap_jumbo_pattern' => null,
                    'dpr_unit_pattern' => '^(C7|C577)', 'dpr_class_pattern' => '^(E39|E539)', 'dpr_jumbo' => false,
                    'target_kind' => 'distance', 'kombinasi' => '395', 'dist_bucket' => '395',
                ],
                'hd785_1250' => [
                    'label' => 'HD785 VS 1250', 'is_excavator_hm' => false,
                    'sap_prefix_col' => 'kode_unit_2', 'sap_prefix_pattern' => '^(HD78|H57)',
                    'sap_class_pattern' => '^(E12|E61|E512)', 'sap_jumbo_pattern' => null,
                    'dpr_unit_pattern' => '^(HD78|H57)', 'dpr_class_pattern' => '^(E12|E61|E512)', 'dpr_jumbo' => false,
                    'target_kind' => 'distance', 'kombinasi' => '1250_HD785', 'dist_bucket' => '1250',
                ],
                'hd785_jumbo' => [
                    'label' => 'HD785 JUMBO', 'is_excavator_hm' => false,
                    'sap_prefix_col' => 'kode_unit', 'sap_prefix_pattern' => '^(HD78|H57)',
                    'sap_class_pattern' => '^(E39|E539|E12|E61|E512|E20)', 'sap_jumbo_pattern' => '^(JUMBO)',
                    'dpr_unit_pattern' => '^(HD78|H57)', 'dpr_class_pattern' => '^(E39|E539|E12|E61|E512|E20)', 'dpr_jumbo' => true,
                    'target_kind' => 'distance', 'kombinasi' => '1250_JUMBO', 'dist_bucket' => '1250',
                ],
                'c777_1250' => [
                    'label' => 'C777 VS 1250', 'is_excavator_hm' => false,
                    'sap_prefix_col' => 'kode_unit_2', 'sap_prefix_pattern' => '^(C7|C57)',
                    'sap_class_pattern' => '^(E12|E61|E512)', 'sap_jumbo_pattern' => null,
                    'dpr_unit_pattern' => '^(C7|C577)', 'dpr_class_pattern' => '^(E12|E61|E512)', 'dpr_jumbo' => false,
                    'target_kind' => 'distance', 'kombinasi' => '1250_C777', 'dist_bucket' => '1250',
                ],
                'e1250' => [
                    'label' => 'HM E1250', 'is_excavator_hm' => true,
                    'sap_class_col' => 'kode_unit', 'sap_class_pattern' => '^(E12|E512)',
                    'dpr_class_pattern' => '^(E12|E512)',
                    'target_kind' => 'fixed', 'target_fixed' => 400,
                ],
                'e6108' => [
                    'label' => 'HM E6108', 'is_excavator_hm' => true,
                    'sap_class_col' => 'kode_unit', 'sap_class_pattern' => '^(E61)',
                    'dpr_class_pattern' => '^(E61)',
                    'target_kind' => 'fixed', 'target_fixed' => 400,
                ],
                'e395' => [
                    'label' => 'HM E395', 'is_excavator_hm' => true,
                    'sap_class_col' => 'kode_unit', 'sap_class_pattern' => '^(E39|E539)',
                    'dpr_class_pattern' => '^(E39|E539)',
                    'target_kind' => 'fixed', 'target_fixed' => 350,
                ],
            ];
        }

        // OB
        return [
            'hd785_395' => [
                'label' => 'HD785 VS 395', 'is_excavator_hm' => false,
                'sap_prefix_col' => 'kode_unit', 'sap_prefix_pattern' => '^(HD78|H57)',
                'sap_class_pattern' => '^(E39|E539)', 'sap_jumbo_pattern' => null,
                'dpr_unit_pattern' => '^(HD78|H57)', 'dpr_class_pattern' => '^(E39|E539)', 'dpr_jumbo' => false,
                'target_kind' => 'distance', 'kombinasi' => '395', 'dist_bucket' => '395',
            ],
            'c777_395' => [
                'label' => 'C777 VS 395', 'is_excavator_hm' => false,
                'sap_prefix_col' => 'kode_unit', 'sap_prefix_pattern' => '^(C577|C7)',
                'sap_class_pattern' => '^(E39|E539)', 'sap_jumbo_pattern' => null,
                'dpr_unit_pattern' => '^(C7|C57)', 'dpr_class_pattern' => '^(E39|E539)', 'dpr_jumbo' => false,
                'target_kind' => 'distance', 'kombinasi' => '395', 'dist_bucket' => '395',
            ],
            'hd785_1250' => [
                'label' => 'HD785 VS 1250', 'is_excavator_hm' => false,
                'sap_prefix_col' => 'kode_unit', 'sap_prefix_pattern' => '^(HD78|HH57)',
                'sap_class_pattern' => '^(E12|E61|E512)', 'sap_jumbo_pattern' => null,
                'dpr_unit_pattern' => '^(HD78|H57)', 'dpr_class_pattern' => '^(E12|E61|E512)', 'dpr_jumbo' => false,
                'target_kind' => 'distance', 'kombinasi' => '1250', 'dist_bucket' => '1250',
            ],
            'c777_1250' => [
                'label' => 'C777 VS 1250', 'is_excavator_hm' => false,
                'sap_prefix_col' => 'kode_unit', 'sap_prefix_pattern' => '^(C577|C7)',
                'sap_class_pattern' => '^(E12|E61|E512)', 'sap_jumbo_pattern' => null,
                'dpr_unit_pattern' => '^(C7|C577)', 'dpr_class_pattern' => '^(E12|E61|E512)', 'dpr_jumbo' => false,
                'target_kind' => 'distance', 'kombinasi' => '1250', 'dist_bucket' => '1250',
            ],
            'hd785_2000' => [
                'label' => 'HD785 VS 2000', 'is_excavator_hm' => false,
                'sap_prefix_col' => 'kode_unit', 'sap_prefix_pattern' => '^(HD78|H57)',
                'sap_class_pattern' => '^(E20)', 'sap_jumbo_pattern' => null,
                'dpr_unit_pattern' => '^(HD78|H57)', 'dpr_class_pattern' => '^(E20)', 'dpr_jumbo' => false,
                'target_kind' => 'distance', 'kombinasi' => '2000', 'dist_bucket' => '2000',
            ],
            'c777_2000' => [
                'label' => 'C777 VS 2000', 'is_excavator_hm' => false,
                'sap_prefix_col' => 'kode_unit', 'sap_prefix_pattern' => '^(C7|C577)',
                'sap_class_pattern' => '^(E20)', 'sap_jumbo_pattern' => null,
                'dpr_unit_pattern' => '^(C7|C577)', 'dpr_class_pattern' => '^(E20)', 'dpr_jumbo' => false,
                'target_kind' => 'distance', 'kombinasi' => '2000', 'dist_bucket' => '2000',
            ],
            'e2000' => [
                'label' => 'HM E2000', 'is_excavator_hm' => true,
                'sap_class_col' => 'kode_unit', 'sap_class_pattern' => '^(E20)',
                'sap_act_values_override' => ['P02', 'P03', 'P12', 'P13'],
                'dpr_class_pattern' => '^(E20)',
                'target_kind' => 'fixed', 'target_fixed' => 650,
            ],
            'e1250' => [
                'label' => 'HM E1250', 'is_excavator_hm' => true,
                'sap_class_col' => 'kode_unit', 'sap_class_pattern' => '^(E12|E512)',
                'sap_act_values_override' => ['P02', 'P03', 'P12', 'P13'],
                'dpr_class_pattern' => '^(E12|E512)',
                'target_kind' => 'fixed', 'target_fixed' => 450,
            ],
            'e6108' => [
                'label' => 'HM E6108', 'is_excavator_hm' => true,
                'sap_class_col' => 'kode_unit', 'sap_class_pattern' => '^(E61)',
                'sap_act_values_override' => ['P02', 'P03', 'P12', 'P13'],
                'dpr_class_pattern' => '^(E61)',
                'target_kind' => 'fixed', 'target_fixed' => 450,
            ],
            'e395' => [
                'label' => 'HM E395', 'is_excavator_hm' => true,
                'sap_class_col' => 'kode_unit', 'sap_class_pattern' => '^(E39|539)',
                'sap_act_values_override' => ['P02', 'P03', 'P12', 'P13'],
                'dpr_class_pattern' => '^(E39|E539)',
                'target_kind' => 'fixed', 'target_fixed' => 400,
            ],
        ];
    }

    public static function distanceBucketDefs(string $kategori): array
    {
        if ($kategori === 'COAL') {
            return [
                '395'  => ['dpr_class_pattern' => '^(E39|E539)', 'dpr_jumbo' => false],
                '1250' => ['dpr_class_pattern' => '^(E12|E61|E512|E39|E539|E20)', 'dpr_jumbo' => false],
            ];
        }

        return [
            '395'  => ['dpr_class_pattern' => '^(E39|E539)', 'dpr_jumbo' => false],
            '1250' => ['dpr_class_pattern' => '^(E12|E61|E512)', 'dpr_jumbo' => false],
            '2000' => ['dpr_class_pattern' => '^(E20)', 'dpr_jumbo' => false],
        ];
    }

    public function calculateForPeriod(int $tahun, int $quartal, ?array $nrps = null): array
    {
        [$start, $end] = self::quarterRange($tahun, $quartal);

        if ($nrps === null) {
            $nrps = DB::table('qor_dpr')
                ->whereBetween('tanggal', [$start, $end])
                ->whereNotNull('nrp')
                ->distinct()
                ->pluck('nrp')
                ->merge(
                    DB::table('qor_sap_hm')
                        ->whereBetween('tanggal', [$start, $end])
                        ->whereNotNull('nrp')
                        ->distinct()
                        ->pluck('nrp')
                )
                ->unique()
                ->values()
                ->all();
        }

        $processed = 0;
        foreach ($nrps as $nrp) {
            $this->computeAndSaveForNrp($nrp, $tahun, $quartal);
            $processed++;
        }

        return [
            'total_nrp' => count($nrps),
            'processed' => $processed,
            'tahun' => $tahun,
            'quartal' => $quartal,
        ];
    }

    public function computeAndSaveForNrp(string $nrp, int $tahun, int $quartal): void
    {
        [$start, $end] = self::quarterRange($tahun, $quartal);
        $nama = $this->lookupNama($nrp);

        // Compute COAL and OB
        $coalRes = $this->computeForNrpCategory($nrp, $start, $end, 'COAL');
        $obRes = $this->computeForNrpCategory($nrp, $start, $end, 'OB');

        // Combined achievement
        $coalPct = $coalRes['achievement_pct'];
        $obPct = $obRes['achievement_pct'];
        $coalHm = $coalRes['hm_total'];
        $obHm = $obRes['hm_total'];

        $combinedPct = null;
        if ($coalPct !== null && $obPct !== null) {
            $sumHm = $coalHm + $obHm;
            $combinedPct = ($sumHm > 0)
                ? round((($coalPct * $coalHm) + ($obPct * $obHm)) / $sumHm, 4)
                : round(($coalPct + $obPct) / 2, 4);
        } elseif ($coalPct !== null) {
            $combinedPct = $coalPct;
        } elseif ($obPct !== null) {
            $combinedPct = $obPct;
        }

        $coalRes['achievement_combined'] = $combinedPct;
        $obRes['achievement_combined'] = $combinedPct;

        $this->saveResult($nrp, $nama, $tahun, $quartal, 'COAL', $coalRes);
        $this->saveResult($nrp, $nama, $tahun, $quartal, 'OB', $obRes);
    }

    private function computeForNrpCategory(string $nrp, string $start, string $end, string $kategori): array
    {
        $defs = self::categoryDefs($kategori);
        $bucketDefs = self::distanceBucketDefs($kategori);

        $row = [];
        $unresolved = [];

        // 1. Distance buckets
        foreach ($bucketDefs as $bKey => $bDef) {
            $row['dist_bucket_' . $bKey] = $this->calcDistanceBucket($nrp, $start, $end, $kategori, $bDef);
        }

        // 2. Per category calculations
        $hmTotal = 0.0;
        $sumTargetTimesHm = 0.0;
        $sumActTimesHm = 0.0;

        foreach ($defs as $col => $cDef) {
            $hm = $this->sumSapHm($nrp, $start, $end, $cDef);
            $act = $this->sumDprActual($nrp, $start, $end, $kategori, $cDef);

            $row['hm_' . $col] = $hm;
            $row['act_' . $col] = $act;

            // Productivity
            $pdty = ($hm > 0) ? round($act / $hm, 4) : null;
            $row['pdty_' . $col] = $pdty;

            // Target
            $target = null;
            if ($cDef['target_kind'] === 'fixed') {
                $target = (float)$cDef['target_fixed'];
            } elseif ($cDef['target_kind'] === 'distance') {
                $bKey = $cDef['dist_bucket'];
                $distVal = $row['dist_bucket_' . $bKey] ?? null;
                $kombinasi = $cDef['kombinasi'];

                if ($distVal !== null && $distVal > 0) {
                    $target = $this->targetLookup($kategori, $kombinasi, $distVal);
                    if ($target === null) {
                        $unresolved[] = "{$col}: dist={$distVal}, komb={$kombinasi}";
                    }
                }
            }
            $row['target_' . $col] = $target;

            // Sumproducts
            if ($hm > 0) {
                $hmTotal += $hm;
                if ($pdty !== null) {
                    $sumActTimesHm += ($pdty * $hm);
                }
                if ($target !== null) {
                    $sumTargetTimesHm += ($target * $hm);
                }
            }
        }

        $row['hm_total'] = round($hmTotal, 2);
        $row['sumproduct_target'] = ($hmTotal > 0) ? round($sumTargetTimesHm / $hmTotal, 4) : null;
        $row['sumproduct_act'] = ($hmTotal > 0) ? round($sumActTimesHm / $hmTotal, 4) : null;

        $achievPct = null;
        if ($row['sumproduct_target'] > 0 && $row['sumproduct_act'] !== null) {
            $achievPct = round(($row['sumproduct_act'] / $row['sumproduct_target']) * 100, 4);
        }
        $row['achievement_pct'] = $achievPct;
        $row['target_unresolved'] = !empty($unresolved) ? implode('; ', $unresolved) : null;

        return $row;
    }

    private function sumSapHm(string $nrp, string $start, string $end, array $def): float
    {
        $query = DB::table('qor_sap_hm')
            ->where('nrp', $nrp)
            ->whereBetween('tanggal', [$start, $end]);

        if (!empty($def['is_excavator_hm'])) {
            $col = $def['sap_class_col'] ?? 'kode_unit';
            $query->where($col, 'REGEXP', $def['sap_class_pattern']);
            if (!empty($def['sap_act_values_override'])) {
                $query->whereIn('aktivitas', $def['sap_act_values_override']);
            }
        } else {
            $col = $def['sap_prefix_col'] ?? 'kode_unit';
            $query->where($col, 'REGEXP', $def['sap_prefix_pattern']);
            if (!empty($def['sap_class_pattern'])) {
                $query->where('alat_muat', 'REGEXP', $def['sap_class_pattern']);
            }
        }

        $val = (float)$query->sum('hour_time');
        return round($val, 2);
    }

    private function sumDprActual(string $nrp, string $start, string $end, string $kategori, array $def): float
    {
        $query = DB::table('qor_dpr')
            ->where('nrp', $nrp)
            ->whereBetween('tanggal', [$start, $end]);

        if ($kategori === 'COAL') {
            $query->where(function ($q) {
                $q->where('source', 'COAL')->orWhereNull('source');
            });
            $sumCol = 'total_ton';
        } else {
            $query->where(function ($q) {
                $q->where('source', 'OB')->orWhereNull('source');
            });
            $sumCol = 'total_bcm';
        }

        if (!empty($def['is_excavator_hm'])) {
            $query->where('role_op', 'EXCA');
            $query->where('loader', 'REGEXP', $def['dpr_class_pattern']);
        } else {
            $query->where('role_op', '!=', 'EXCA');
            if (!empty($def['dpr_unit_pattern'])) {
                $query->where('kode_unit', 'REGEXP', $def['dpr_unit_pattern']);
            }
            if (!empty($def['dpr_class_pattern'])) {
                $query->where('loader', 'REGEXP', $def['dpr_class_pattern']);
            }
        }

        $val = (float)$query->sum($sumCol);
        return round($val, 4);
    }

    private function calcDistanceBucket(string $nrp, string $start, string $end, string $kategori, array $bDef): ?float
    {
        $query = DB::table('qor_dpr')
            ->where('nrp', $nrp)
            ->whereBetween('tanggal', [$start, $end])
            ->where('role_op', '!=', 'EXCA');

        if ($kategori === 'COAL') {
            $query->where(function ($q) { $q->where('source', 'COAL')->orWhereNull('source'); });
            $sumProdCol = DB::raw('SUM(distance * total_ton) as sum_dist_vol');
            $sumVolCol = DB::raw('SUM(total_ton) as sum_vol');
        } else {
            $query->where(function ($q) { $q->where('source', 'OB')->orWhereNull('source'); });
            $sumProdCol = DB::raw('SUM(distance * total_bcm) as sum_dist_vol');
            $sumVolCol = DB::raw('SUM(total_bcm) as sum_vol');
        }

        if (!empty($bDef['dpr_class_pattern'])) {
            $query->where('loader', 'REGEXP', $bDef['dpr_class_pattern']);
        }

        $res = $query->select($sumProdCol, $sumVolCol)->first();
        if (!$res || empty($res->sum_vol) || (float)$res->sum_vol == 0) {
            return null;
        }

        return round(((float)$res->sum_dist_vol / (float)$res->sum_vol) * 1000, 2);
    }

    private function targetLookup(string $kategori, string $kombinasi, float $distance): ?float
    {
        $row = DB::table('target_by_distance')
            ->where('kategori', $kategori)
            ->where('kombinasi', $kombinasi)
            ->where('jarak_min', '<=', $distance)
            ->orderByDesc('jarak_min')
            ->first();

        return $row ? (float)$row->target_pdty : null;
    }

    private function lookupNama(string $nrp): ?string
    {
        $emp = DB::table('employees')->where('nrp', $nrp)->first();
        if ($emp && !empty($emp->name)) return $emp->name;

        $sap = DB::table('qor_sap_hm')->where('nrp', $nrp)->whereNotNull('nama')->first();
        if ($sap) return $sap->nama;

        $dpr = DB::table('qor_dpr')->where('nrp', $nrp)->whereNotNull('nama')->first();
        return $dpr?->nama ?? $nrp;
    }

    private function saveResult(string $nrp, ?string $nama, int $tahun, int $quartal, string $kategori, array $data): void
    {
        QorPdty::updateOrInsert(
            ['nrp' => $nrp, 'tahun' => $tahun, 'quartal' => $quartal, 'kategori' => $kategori],
            array_merge($data, [
                'nama' => $nama,
                'updated_at' => now(),
            ])
        );
    }
}
