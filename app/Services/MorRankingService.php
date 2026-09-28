<?php

namespace App\Services;

use App\Models\MorRanking;
use Illuminate\Support\Facades\DB;

class MorRankingService
{
    public static function categoryDefs(?string $grup): ?array
    {
        if (!$grup) return null;

        $atrFull = [
            83=>5.00, 84=>6.25, 85=>7.50, 86=>8.75, 87=>10.00, 88=>11.25, 89=>12.50, 90=>13.75,
            91=>15.00, 92=>16.25, 93=>17.50, 94=>18.75, 95=>20.00, 96=>21.25, 97=>22.50, 98=>23.75, 99=>25.00,
        ];
        $atrScaled = [
            83=>3.00, 84=>3.75, 85=>4.50, 86=>5.25, 87=>6.00, 88=>6.75, 89=>7.50, 90=>8.25,
            91=>9.00, 92=>9.75, 93=>10.50, 94=>11.25, 95=>12.00, 96=>12.75, 97=>13.50, 98=>14.25, 99=>15.00,
        ];
        $ts = [0=>7, 90=>8, 95=>9, 100=>10];

        $hmA = [0=>4, 34=>5.6, 43=>7.2, 52=>8.8, 61=>10.4, 70=>12, 79=>13.6, 88=>15.2, 97=>16.8, 106=>18.4, 115=>20];
        $hmB = [0=>2, 10=>4, 20=>6, 30=>8, 40=>10, 50=>12, 60=>14, 70=>16, 80=>18, 90=>20, 101=>16, 111=>14];
        $hmC = [0=>0, 10=>2, 20=>4, 30=>6, 40=>8, 50=>10, 60=>12, 70=>14, 80=>16, 90=>18, 101=>20, 111=>18];
        $hmD = [0=>0, 34=>1, 43=>2, 52=>3, 61=>4, 70=>5, 79=>6, 88=>7, 97=>8, 106=>9, 115=>10];

        $pdtyHD = [
            0=>20.00, 20=>20.00, 25=>20.83, 30=>21.67, 35=>22.50, 40=>23.33, 45=>24.17, 50=>25.00, 55=>25.83, 60=>26.67,
            65=>27.50, 70=>28.33, 75=>29.17, 80=>30.00, 85=>30.83, 90=>31.67, 95=>32.50, 100=>33.33, 105=>34.17, 110=>35.00,
            115=>35.83, 120=>36.67, 125=>37.50, 130=>38.33, 135=>39.17, 140=>40.00
        ];
        $pdtyPC = [
            0=>20.00, 20=>21.25, 25=>22.50, 30=>23.75, 35=>25.00, 40=>26.25, 45=>27.50, 50=>28.75, 55=>30.00, 60=>31.25,
            65=>32.50, 70=>33.75, 75=>35.00, 80=>36.25, 85=>37.50, 90=>38.75, 95=>40.00
        ];

        $sets = [
            'A'    => ['atr'=>$atrFull, 'ts'=>$ts, 'hm'=>$hmA, 'pdty'=>null, 'atr_max'=>25, 'ts_max'=>10, 'hm_max'=>20, 'pdty_max'=>0],
            'B'    => ['atr'=>$atrFull, 'ts'=>$ts, 'hm'=>$hmB, 'pdty'=>null, 'atr_max'=>25, 'ts_max'=>10, 'hm_max'=>20, 'pdty_max'=>0],
            'C'    => ['atr'=>$atrFull, 'ts'=>$ts, 'hm'=>$hmC, 'pdty'=>null, 'atr_max'=>25, 'ts_max'=>10, 'hm_max'=>20, 'pdty_max'=>0],
            'D_HD' => ['atr'=>$atrScaled, 'ts'=>$ts, 'hm'=>$hmD, 'pdty'=>$pdtyHD, 'atr_max'=>15, 'ts_max'=>10, 'hm_max'=>10, 'pdty_max'=>40],
            'D_PC' => ['atr'=>$atrScaled, 'ts'=>$ts, 'hm'=>$hmD, 'pdty'=>$pdtyPC, 'atr_max'=>15, 'ts_max'=>10, 'hm_max'=>10, 'pdty_max'=>40],
        ];

        return $sets[$grup] ?? null;
    }

    public static function bracketLookup(array $table, ?float $value): float
    {
        if ($value === null) return 0.0;
        $best = null;
        foreach ($table as $min => $point) {
            if ($value >= $min && ($best === null || $min > $best)) {
                $best = $min;
            }
        }
        return $best === null ? 0.0 : (float)$table[$best];
    }

    public static function mapJabatanToKategori(?string $jabatan): ?array
    {
        $raw = strtoupper(trim((string)$jabatan));
        if ($raw === '') return null;
        $norm = preg_replace('/[^A-Z0-9]/', '', $raw);

        if (str_contains($norm, 'HD785') || str_contains($norm, 'OHT') || str_contains($norm, 'JUMBO') || str_contains($norm, 'HAULER')) {
            return ['kategori' => 'HD 785', 'grup' => 'D_HD'];
        }
        if (str_contains($norm, '395') || str_contains($norm, 'CAT395') || str_contains($norm, 'PC395')) {
            return ['kategori' => 'CAT 395', 'grup' => 'D_PC'];
        }
        if (str_contains($norm, '1250') || str_contains($norm, '6015') || str_contains($norm, '6105') || str_contains($norm, '6108')) {
            return ['kategori' => 'PC 1250/6015', 'grup' => 'D_PC'];
        }
        if (str_contains($norm, '2000') || str_contains($norm, 'PC2000')) {
            return ['kategori' => 'PC 2000', 'grup' => 'D_PC'];
        }
        if (str_contains($norm, 'D85')) return ['kategori' => 'D85', 'grup' => 'B'];
        if (str_contains($norm, 'D155')) return ['kategori' => 'D155', 'grup' => 'B'];
        if (str_contains($norm, 'PC200')) return ['kategori' => 'PC 200', 'grup' => 'B'];
        if (str_contains($norm, 'PC300')) return ['kategori' => 'PC 300', 'grup' => 'A'];
        if (str_contains($norm, '773')) return ['kategori' => 'CAT 773', 'grup' => 'A'];
        if (str_contains($norm, 'GD755') || str_contains($norm, 'GD825') || str_contains($norm, 'GRADER')) {
            return ['kategori' => 'GD', 'grup' => 'C'];
        }

        return ['kategori' => $raw, 'grup' => 'D_HD']; // fallback
    }

    public function calculateForPeriod(int $tahun, int $quartal): array
    {
        [$start, $end] = QorPdtyService::quarterRange($tahun, $quartal);

        $nrps = DB::table('qor_dpr')
            ->whereBetween('tanggal', [$start, $end])
            ->whereNotNull('nrp')
            ->distinct()
            ->pluck('nrp')
            ->merge(
                DB::table('qor_pdty')
                    ->where('tahun', $tahun)
                    ->where('quartal', $quartal)
                    ->distinct()
                    ->pluck('nrp')
            )
            ->unique()
            ->values()
            ->all();

        foreach ($nrps as $nrp) {
            $this->computeForNrp($nrp, $tahun, $quartal, $start, $end);
        }

        // Rank employees by total points descending
        $ranked = MorRanking::query()
            ->where('tahun', $tahun)
            ->where('quartal', $quartal)
            ->orderByDesc('total_poin')
            ->get();

        $rank = 1;
        foreach ($ranked as $item) {
            $item->update(['ranking' => $rank++]);
        }

        return [
            'total' => $ranked->count(),
            'tahun' => $tahun,
            'quartal' => $quartal,
        ];
    }

    private function computeForNrp(string $nrp, int $tahun, int $quartal, string $start, string $end): void
    {
        $emp = DB::table('employees')->where('nrp', $nrp)->first();
        $nama = $emp?->name ?? $nrp;
        $jabatan = $emp?->jabatan_ss6 ?? $emp?->jabatan_roster ?? 'OPERATOR';

        $katInfo = self::mapJabatanToKategori($jabatan);
        $kategori = $katInfo['kategori'] ?? null;
        $grup = $katInfo['grup'] ?? 'D_HD';
        $defs = self::categoryDefs($grup);

        // 1. ATR calculation
        $atrPersen = null;
        $atrPoint = 0.0;
        $atrTotal = DB::table('qor_atr_attendance')->where('nrp', $nrp)->whereBetween('tanggal', [$start, $end])->count();
        if ($atrTotal > 0) {
            $atrPresent = DB::table('qor_atr_attendance')->where('nrp', $nrp)->whereBetween('tanggal', [$start, $end])
                ->whereNotIn('code', ['A', 'I', 'S'])->count();
            $atrPersen = round(($atrPresent / $atrTotal) * 100, 2);
            $atrPoint = self::bracketLookup($defs['atr'], $atrPersen);
        } else {
            // fallback: check attendance_details
            $attTotal = DB::table('attendance_details')->where('nrp', $nrp)->whereBetween('date', [$start, $end])->count();
            if ($attTotal > 0) {
                $attPresent = DB::table('attendance_details')->where('nrp', $nrp)->whereBetween('date', [$start, $end])
                    ->whereNotIn('status', ['ALPHA', 'IZIN', 'SAKIT'])->count();
                $atrPersen = round(($attPresent / $attTotal) * 100, 2);
                $atrPoint = self::bracketLookup($defs['atr'], $atrPersen);
            }
        }

        // 2. Timesheet
        $tsRow = DB::table('qor_timesheet')->where('nrp', $nrp)->where('tahun', $tahun)->where('quartal', $quartal)->first();
        $tsPersen = $tsRow ? (float)$tsRow->achievement : 95.0; // default 95%
        $tsPoint = self::bracketLookup($defs['ts'], $tsPersen);

        // 3. HM Ratio
        $sapHm = (float)DB::table('qor_sap_hm')->where('nrp', $nrp)->whereBetween('tanggal', [$start, $end])->sum('hour_time');
        $workdays = max(1, $atrTotal ?: 60);
        $hmTarget = $workdays * 7.0;
        $hmPersen = round(($sapHm / $hmTarget) * 100, 2);
        $hmPoint = self::bracketLookup($defs['hm'], $hmPersen);

        // 4. PDTY % from QOR PDTY
        $pdtyRow = DB::table('qor_pdty')->where('nrp', $nrp)->where('tahun', $tahun)->where('quartal', $quartal)->first();
        $pdtyPersen = $pdtyRow ? (float)$pdtyRow->achievement_combined : null;
        $pdtyPoint = ($defs['pdty'] && $pdtyPersen !== null) ? self::bracketLookup($defs['pdty'], $pdtyPersen) : 0.0;

        // 5. Total points
        $totalPoin = round($atrPoint + $tsPoint + $hmPoint + $pdtyPoint, 2);

        MorRanking::updateOrInsert(
            ['nrp' => $nrp, 'tahun' => $tahun, 'quartal' => $quartal],
            [
                'nama' => $nama,
                'jabatan' => $jabatan,
                'kategori' => $kategori,
                'grup' => $grup,
                'atr_persen' => $atrPersen,
                'atr_point' => $atrPoint,
                'ts_persen' => $tsPersen,
                'ts_point' => $tsPoint,
                'hm_persen' => $hmPersen,
                'hm_point' => $hmPoint,
                'pdty_persen' => $pdtyPersen,
                'pdty_point' => $pdtyPoint,
                'fuel_point' => 0,
                'pembinaan_point' => 0,
                'total_poin' => $totalPoin,
                'fuel_pending' => true,
                'pembinaan_pending' => true,
                'grup_unmapped' => false,
                'updated_at' => now(),
            ]
        );
    }
}
