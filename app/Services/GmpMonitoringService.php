<?php

namespace App\Services;

use App\Enums\GmpFindingStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GmpMonitoringService
{
    public const ALL_TYPES = ['MHR', 'MFOB', 'MFCO', 'DISPOSAL', 'DEWATERING'];

    public const TARGET_SCORE = 95;

    public const PERCENT_MAP = [
        0 => 0,
        1 => 20,
        2 => 40,
        3 => 60,
        4 => 80,
        5 => 100,
    ];

    public function getData(string $type = 'MHR', ?string $year = null, ?string $week = null, ?string $aspekArea = null): array
    {
        $type = strtoupper(trim($type ?: 'MHR'));
        if (! in_array($type, self::ALL_TYPES, true)) {
            $type = 'MHR';
        }

        $year = trim((string) $year);
        $week = trim((string) $week);

        $aspekArea = strtoupper(trim($aspekArea ?: $type));
        if (! in_array($aspekArea, self::ALL_TYPES, true)) {
            $aspekArea = $type;
        }

        // 1. Filter Options (Tahun & Week)
        $years = DB::table('gmp_penilaian_form')
            ->where('status', 'approved')
            ->whereNotNull('tanggal_gmp')
            ->selectRaw('DISTINCT YEAR(tanggal_gmp) as y')
            ->orderByDesc('y')
            ->pluck('y')
            ->map(fn ($y) => (string) $y)
            ->all();

        $weekQuery = DB::table('gmp_penilaian_form')
            ->where('status', 'approved')
            ->where('tipe_gmp', $type);
        if ($year !== '') {
            $weekQuery->whereYear('tanggal_gmp', $year);
        }
        $weeks = $weekQuery->groupBy('week')
            ->select('week', DB::raw('MAX(tanggal_gmp) as mx'))
            ->orderByDesc('mx')
            ->pluck('week')
            ->all();

        // 2. Statistik Kartu (area + tahun + week terpilih)
        $cardQuery = DB::table('gmp_penilaian_form')
            ->where('status', 'approved')
            ->where('tipe_gmp', $type);
        if ($week !== '') {
            $cardQuery->where('week', $week);
        } elseif ($year !== '') {
            $cardQuery->whereYear('tanggal_gmp', $year);
        }

        $cardRow = (clone $cardQuery)
            ->selectRaw('COUNT(*) as total, AVG(total_percent) as avg_pct, COUNT(DISTINCT lokasi) as lok')
            ->first();

        // Lokasi Critical (< 50)
        $critQuery = (clone $cardQuery)
            ->groupBy('lokasi')
            ->havingRaw('AVG(total_percent) < 50')
            ->select('lokasi');
        $criticalCount = DB::table(DB::raw("({$critQuery->toSql()}) as t"))
            ->mergeBindings($critQuery)
            ->count();

        $cards = [
            'total_penilaian' => (int) ($cardRow->total ?? 0),
            'avg_skor' => round((float) ($cardRow->avg_pct ?? 0), 1),
            'jumlah_lokasi' => (int) ($cardRow->lok ?? 0),
            'critical_lokasi' => $criticalCount,
        ];

        // 3. Trend Akumulasi GMP (Semua Area, per week)
        $trendQuery = DB::table('gmp_penilaian_form')
            ->where('status', 'approved');
        if ($year !== '') {
            $trendQuery->whereYear('tanggal_gmp', $year);
        }
        $trendRows = $trendQuery->groupBy('tipe_gmp', 'week')
            ->select('tipe_gmp', 'week', DB::raw('AVG(total_percent) as avg_pct'), DB::raw('MAX(tanggal_gmp) as mx'))
            ->get();

        $weekMax = [];
        $byType = [];
        foreach ($trendRows as $r) {
            $w = $r->week;
            $tt = strtoupper($r->tipe_gmp);
            $weekMax[$w] = max($weekMax[$w] ?? '', (string) $r->mx);
            $byType[$tt][$w] = (float) $r->avg_pct;
        }
        uasort($weekMax, fn ($a, $b) => strcmp((string) $a, (string) $b));
        $sortedWeeks = array_keys($weekMax);

        $trendFull = [];
        $trendByType = [];
        foreach (self::ALL_TYPES as $tt) {
            $trendByType[$tt] = [];
        }
        foreach ($sortedWeeks as $w) {
            $sum = 0;
            $cnt = 0;
            foreach (self::ALL_TYPES as $tt) {
                $v = $byType[$tt][$w] ?? null;
                $trendByType[$tt][] = ($v !== null) ? round($v, 1) : null;
                if ($v !== null) {
                    $sum += $v;
                    $cnt++;
                }
            }
            $trendFull[] = $cnt > 0 ? round($sum / $cnt, 1) : null;
        }

        // 4. Skor per Aspek GMP (area aspek + tahun + week)
        $aspekQuery = DB::table('gmp_penilaian_form as f')
            ->join('gmp_penilaian_form_detail as d', 'f.id', '=', 'd.form_id')
            ->where('f.status', 'approved')
            ->where('f.tipe_gmp', $aspekArea);
        if ($week !== '') {
            $aspekQuery->where('f.week', $week);
        } elseif ($year !== '') {
            $aspekQuery->whereYear('f.tanggal_gmp', $year);
        }

        $aspekRows = $aspekQuery->select('d.section_key', 'd.score_raw')->get();
        $aspSum = [];
        $aspCnt = [];
        foreach ($aspekRows as $r) {
            $sec = strtolower((string) $r->section_key);
            $pct = self::PERCENT_MAP[(int) $r->score_raw] ?? 0;
            $aspSum[$sec] = ($aspSum[$sec] ?? 0) + $pct;
            $aspCnt[$sec] = ($aspCnt[$sec] ?? 0) + 1;
        }

        $aspectLabels = [
            'teknis' => 'Teknis',
            'keselamatan' => 'Keselamatan',
            'optimalisasi' => 'Optimalisasi',
            'lingkungan' => 'Lingkungan',
        ];
        $aspek = [
            'area' => $aspekArea,
            'labels' => [],
            'values' => [],
        ];
        foreach ($aspectLabels as $key => $lbl) {
            if (! empty($aspCnt[$key])) {
                $aspek['labels'][] = $lbl;
                $aspek['values'][] = round($aspSum[$key] / $aspCnt[$key], 1);
            }
        }

        // 5. Skor per Lokasi (area + tahun + week)
        $lokQuery = DB::table('gmp_penilaian_form')
            ->where('status', 'approved')
            ->where('tipe_gmp', $type);
        if ($week !== '') {
            $lokQuery->where('week', $week);
        } elseif ($year !== '') {
            $lokQuery->whereYear('tanggal_gmp', $year);
        }

        $lokRows = $lokQuery->groupBy('lokasi')
            ->select('lokasi', DB::raw('AVG(total_percent) as avg_pct'))
            ->orderByDesc('avg_pct')
            ->get();

        $lokLabels = [];
        $lokValues = [];
        foreach ($lokRows as $r) {
            $lokLabels[] = $r->lokasi;
            $lokValues[] = round((float) $r->avg_pct, 1);
        }

        // 6. KPI Pengendalian
        $aktual = $cards['avg_skor'];
        $belowTarget = 0;
        foreach ($lokValues as $v) {
            if ($v < self::TARGET_SCORE) {
                $belowTarget++;
            }
        }

        // Temuan per Area (Backlog berjalan)
        $temRow = DB::table('gmp_temuan')
            ->whereNull('deleted_at')
            ->whereRaw('UPPER(area_id) = ?', [$type])
            ->selectRaw("
                SUM(CASE WHEN status <> 'closed' THEN 1 ELSE 0 END) AS open_cnt,
                SUM(CASE WHEN status NOT IN ('closed','waiting_verification','draft','rework') AND due_date IS NOT NULL AND due_date < ? THEN 1 ELSE 0 END) AS overdue_cnt,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) AS closed_cnt,
                COUNT(*) AS total_cnt
            ", [now()->toDateString()])
            ->first();

        $temuanOpen = (int) ($temRow->open_cnt ?? 0);
        $temuanOverdue = (int) ($temRow->overdue_cnt ?? 0);
        $temuanClosed = (int) ($temRow->closed_cnt ?? 0);
        $temuanTotal = (int) ($temRow->total_cnt ?? 0);
        $closureRate = $temuanTotal > 0 ? round($temuanClosed / $temuanTotal * 100, 1) : 0;

        // Kelengkapan Inspeksi
        $targetWeek = $week !== '' ? $week : ($weeks[0] ?? '');
        $masterCount = DB::table('gmp_master_lokasi')->where('tipe_gmp', $type)->count();
        $assessedCount = 0;
        if ($targetWeek !== '') {
            $assessedCount = DB::table('gmp_penilaian_form')
                ->where('status', 'approved')
                ->where('tipe_gmp', $type)
                ->where('week', $targetWeek)
                ->distinct('lokasi')
                ->count('lokasi');
        }
        $belumDinilai = max(0, $masterCount - $assessedCount);

        // Peningkatan dari minggu lalu
        $dwQuery = DB::table('gmp_penilaian_form')
            ->where('status', 'approved')
            ->where('tipe_gmp', $type);
        if ($year !== '') {
            $dwQuery->whereYear('tanggal_gmp', $year);
        }
        $weekAvgs = $dwQuery->groupBy('week')
            ->select('week', DB::raw('AVG(total_percent) as avg_pct'), DB::raw('MAX(tanggal_gmp) as mx'))
            ->orderBy('mx', 'asc')
            ->get();

        $curIdx = -1;
        if ($week !== '') {
            foreach ($weekAvgs as $i => $wa) {
                if ($wa->week === $week) {
                    $curIdx = $i;
                    break;
                }
            }
        } else {
            $curIdx = $weekAvgs->count() - 1;
        }

        $improvement = null;
        $curWeekLabel = null;
        $prevWeekLabel = null;
        if ($curIdx >= 1) {
            $improvement = round((float) $weekAvgs[$curIdx]->avg_pct - (float) $weekAvgs[$curIdx - 1]->avg_pct, 1);
            $curWeekLabel = $weekAvgs[$curIdx]->week;
            $prevWeekLabel = $weekAvgs[$curIdx - 1]->week;
        }

        $kpi = [
            'aktual' => $aktual,
            'target' => self::TARGET_SCORE,
            'below_target' => $belowTarget,
            'temuan_open' => $temuanOpen,
            'temuan_overdue' => $temuanOverdue,
            'closure_rate' => $closureRate,
            'belum_dinilai' => $belumDinilai,
            'master_lokasi' => $masterCount,
            'assessed_week' => $assessedCount,
            'target_week' => $targetWeek,
            'improvement' => $improvement,
            'prev_week' => $prevWeekLabel,
            'cur_week' => $curWeekLabel,
        ];

        // Pesan otomatis
        $messages = [];
        if ($belowTarget > 0) {
            $messages[] = [
                'level' => 'warning',
                'text' => "{$belowTarget} area masih berada di bawah target GMP \u{2265} ".self::TARGET_SCORE.'% dan memerlukan tindak lanjut prioritas.',
            ];
        } elseif (! empty($lokValues)) {
            $messages[] = [
                'level' => 'success',
                'text' => "Semua area sudah mencapai target GMP \u{2265} ".self::TARGET_SCORE.'%.',
            ];
        }
        if ($temuanOverdue > 0) {
            $messages[] = [
                'level' => 'danger',
                'text' => "{$temuanOverdue} temuan overdue melewati target penyelesaian dan perlu segera ditindaklanjuti.",
            ];
        }
        if ($belumDinilai > 0 && $targetWeek !== '') {
            $messages[] = [
                'level' => 'warning',
                'text' => "{$belumDinilai} lokasi belum dinilai pada {$targetWeek} — kelengkapan inspeksi belum tercapai.",
            ];
        }

        // 7. Pareto Ketidaksesuaian GMP
        $paretoQuery = DB::table('gmp_temuan')
            ->whereNull('deleted_at');
        if ($year !== '') {
            $paretoQuery->whereYear('finding_date', $year);
        }
        $prows = $paretoQuery->selectRaw("COALESCE(NULLIF(checklist_id, ''), NULLIF(aspect_id, ''), 'Lainnya') AS kategori, COUNT(*) AS cnt")
            ->groupBy('kategori')
            ->orderByDesc('cnt')
            ->get();

        $maxBars = 14;
        $items = $prows->all();
        if (count($items) > $maxBars) {
            $head = array_slice($items, 0, $maxBars);
            $tailSum = array_sum(array_map(fn ($r) => (int) $r->cnt, array_slice($items, $maxBars)));
            if ($tailSum > 0) {
                $head[] = (object) ['kategori' => 'Lainnya', 'cnt' => $tailSum];
            }
            $items = $head;
        }

        $grand = array_sum(array_map(fn ($r) => (int) $r->cnt, $items));
        $paretoLabels = [];
        $paretoCounts = [];
        $paretoCumulative = [];
        $running = 0;
        foreach ($items as $r) {
            $c = (int) $r->cnt;
            $paretoLabels[] = $r->kategori;
            $paretoCounts[] = $c;
            $running += $c;
            $paretoCumulative[] = $grand > 0 ? round($running / $grand * 100, 1) : 0;
        }

        $paretoTop3 = [];
        foreach (array_slice($items, 0, 3) as $r) {
            $paretoTop3[] = [
                'label' => $r->kategori,
                'count' => (int) $r->cnt,
                'pct' => $grand > 0 ? round((int) $r->cnt / $grand * 100, 1) : 0,
            ];
        }

        // Area penyumbang terbesar
        $topAreaQuery = DB::table('gmp_temuan')
            ->whereNull('deleted_at')
            ->whereNotNull('area_id')
            ->where('area_id', '<>', '');
        if ($year !== '') {
            $topAreaQuery->whereYear('finding_date', $year);
        }
        $topArea = $topAreaQuery->select('area_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('area_id')
            ->orderByDesc('cnt')
            ->first();

        $paretoTopArea = $topArea ? ['area' => $topArea->area_id, 'count' => (int) $topArea->cnt] : null;

        $pareto = [
            'labels' => $paretoLabels,
            'counts' => $paretoCounts,
            'cumulative' => $paretoCumulative,
            'total' => $grand,
            'top3' => $paretoTop3,
            'top_area' => $paretoTopArea,
        ];

        if (! empty($paretoTop3)) {
            $t1 = $paretoTop3[0];
            $messages[] = [
                'level' => 'info',
                'text' => "Masalah GMP terbesar: {$t1['label']} ({$t1['count']} temuan, {$t1['pct']}% dari total).",
            ];
        }

        return [
            'filters' => [
                'type' => $type,
                'year' => $year,
                'week' => $week,
                'aspek_area' => $aspekArea,
            ],
            'options' => [
                'years' => $years,
                'weeks' => $weeks,
                'types' => self::ALL_TYPES,
            ],
            'cards' => $cards,
            'kpi' => $kpi,
            'messages' => $messages,
            'trend' => [
                'labels' => $sortedWeeks,
                'full' => $trendFull,
                'byType' => $trendByType,
            ],
            'aspek' => $aspek,
            'lokasi' => [
                'labels' => $lokLabels,
                'values' => $lokValues,
            ],
            'pareto' => $pareto,
        ];
    }
}
