<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard GMP (padanan index.php?type=gmp v1).
 * Skor area = skor mentah TERENDAH per pertanyaan per lokasi, dikonversi ke % lalu dibobot;
 * kartu statistik & chart hanya memakai penilaian yang sudah approved.
 */
class GmpDashboardService
{
    public const TYPE_LABELS = [
        'MFOB' => 'Front OB',
        'MFCO' => 'Front Coal Getting',
        'DISPOSAL' => 'Disposal',
        'MHR' => 'Jalan (MHR)',
        'DEWATERING' => 'Dewatering',
    ];

    private const YEAR_RANGE = 5;

    private const TOP_LOCATIONS = 15;

    private const SECTION_COLORS = [
        'teknis' => '#ea580c',
        'keselamatan' => '#0f766e',
        'optimalisasi' => '#4472C4',
        'lingkungan' => '#10b981',
    ];

    public function resolveType(?string $type): string
    {
        $type = strtoupper((string) $type);

        return array_key_exists($type, self::TYPE_LABELS) ? $type : 'MFOB';
    }

    public function yearOptions(): array
    {
        return range((int) now()->year, (int) now()->year - self::YEAR_RANGE + 1);
    }

    /** Nomor week yang punya data untuk tipe & tahun, terbaru dulu (mis. [37, 36]). */
    public function availableWeeks(string $type, int $year): array
    {
        return $this->forms($type)->whereYear('tanggal_gmp', $year)
            ->select('week', DB::raw('MAX(tanggal_gmp) AS latest'))->groupBy('week')->orderByDesc('latest')
            ->pluck('week')
            ->map(fn ($week) => preg_match('/(\d+)/', (string) $week, $m) ? (int) $m[1] : null)
            ->filter()->unique()->values()->all();
    }

    /** Format kolom week di database: "Week NN - YYYY". */
    public function weekLabel(int $week, int $year): string
    {
        return sprintf('Week %02d - %d', $week, $year);
    }

    /**
     * @param  array  $questions  [no => ['name', 'section_key', ...]]
     * @param  string|null  $weekLabel  null = semua week dalam tahun
     */
    public function build(string $type, array $questions, int $year, ?string $weekLabel, array $availableWeeks): array
    {
        $areaScores = $this->areaScores($type, $year, $weekLabel);
        $values = array_values($areaScores);

        $chartWeek = $weekLabel ?? ($availableWeeks ? $this->weekLabel($availableWeeks[0], $year) : null);
        [$pareto, $locationCharts] = $chartWeek ? $this->weekCharts($type, $questions, $chartWeek) : [[], []];

        return [
            'stats' => [
                'total_penilaian' => $this->forms($type)->whereYear('tanggal_gmp', $year)->when($weekLabel, fn ($q) => $q->where('week', $weekLabel))->count(),
                'avg_score' => $values ? array_sum($values) / count($values) : 0,
                'max_score' => $values ? max($values) : 0,
                'min_score' => $values ? min($values) : 0,
            ],
            'lokasi' => $this->locationAverages($areaScores),
            'chartWeek' => $chartWeek,
            'pareto' => $pareto,
            'locationCharts' => $locationCharts,
        ];
    }

    /** Skor berbobot per area (week + lokasi). */
    private function areaScores(string $type, int $year, ?string $weekLabel): array
    {
        $percentMap = config('gmp_scoring.percent_map');
        $weightMap = config("gmp_scoring.weight_map.{$type}", []);

        $rows = $this->minScores($type)
            ->whereYear('f.tanggal_gmp', $year)
            ->when($weekLabel, fn ($q) => $q->where('f.week', $weekLabel))
            ->groupBy('f.week', 'f.lokasi', 'd.question_no')
            ->select('f.week', 'f.lokasi', 'd.question_no', DB::raw('MIN(d.score_raw) AS min_raw'))
            ->get();

        $scores = [];
        foreach ($rows as $row) {
            $key = $row->week.'||'.$row->lokasi;
            $scores[$key] = ($scores[$key] ?? 0) + ($percentMap[(int) $row->min_raw] ?? 0) * ($weightMap[(int) $row->question_no] ?? 0) / 100;
        }

        return $scores;
    }

    /** Rata-rata skor area per lokasi (gabung antar week), tertinggi dulu, maks 15. */
    private function locationAverages(array $areaScores): array
    {
        return collect($areaScores)
            ->groupBy(fn ($score, $key) => explode('||', $key, 2)[1], true)
            ->map(fn (Collection $scores, $lokasi) => ['lokasi' => $lokasi, 'avg_score' => round($scores->avg(), 2), 'total' => $scores->count()])
            ->sortByDesc('avg_score')
            ->take(self::TOP_LOCATIONS)
            ->values()
            ->all();
    }

    /** Pareto per aspek (rata-rata % antar lokasi) & mini chart per lokasi untuk 1 week. */
    private function weekCharts(string $type, array $questions, string $weekLabel): array
    {
        $percentMap = config('gmp_scoring.percent_map');
        $rows = $this->minScores($type)->where('f.week', $weekLabel)
            ->groupBy('f.lokasi', 'd.question_no')
            ->select('f.lokasi', 'd.question_no', DB::raw('MIN(d.score_raw) AS min_raw'))
            ->orderBy('f.lokasi')->orderBy('d.question_no')
            ->get()
            ->filter(fn ($row) => isset($questions[(int) $row->question_no]));

        if ($rows->isEmpty()) return [[], []];

        $locationCount = $rows->pluck('lokasi')->unique()->count();
        $pareto = collect($questions)->map(fn ($meta, $no) => [
            'label' => $meta['name'],
            'value' => round($rows->where('question_no', $no)->sum(fn ($r) => $percentMap[(int) $r->min_raw] ?? 0) / $locationCount, 2),
        ])->values()->all();

        $locationCharts = $rows->groupBy('lokasi')->map(fn (Collection $group, $lokasi) => [
            'title' => $lokasi,
            'labels' => $group->map(fn ($r) => $questions[(int) $r->question_no]['name'])->values(),
            'data' => $group->map(fn ($r) => $percentMap[(int) $r->min_raw] ?? 0)->values(),
            'colors' => $group->map(fn ($r) => self::SECTION_COLORS[$questions[(int) $r->question_no]['section_key']] ?? '#4472C4')->values(),
        ])->values()->all();

        return [$pareto, $locationCharts];
    }

    /**
     * Trend GMP (padanan index.php?type=trend_gmp v1): rata-rata total_percent per tipe per week,
     * plus FULL = rata-rata antar tipe yang punya data di week tsb. Week diurutkan kronologis.
     *
     * @return array{weeks: array<int,string>, series: array<string, array<string,float>>}
     */
    public function weeklyTrend(int $year): array
    {
        $rows = DB::table('gmp_penilaian_form')->where('status', 'approved')->whereYear('tanggal_gmp', $year)
            ->select('tipe_gmp', 'week', DB::raw('AVG(total_percent) AS avg_pct'))
            ->groupBy('tipe_gmp', 'week')
            ->get();

        $weeks = $rows->pluck('week')->unique()
            ->sortBy(fn ($w) => preg_match('/(\d+)\D+(\d{4})/', (string) $w, $m) ? $m[2] * 100 + $m[1] : 0)
            ->values()->all();

        $series = collect(array_keys(self::TYPE_LABELS))->mapWithKeys(fn ($type) => [$type => $rows
            ->filter(fn ($r) => strtoupper($r->tipe_gmp) === $type)
            ->mapWithKeys(fn ($r) => [$r->week => round((float) $r->avg_pct, 2)])
            ->all()])->all();

        $series['FULL'] = collect($weeks)->mapWithKeys(function ($week) use ($series) {
            $values = collect(self::TYPE_LABELS)->keys()->map(fn ($type) => $series[$type][$week] ?? null)->filter(fn ($v) => $v !== null);

            return $values->isEmpty() ? [] : [$week => round($values->avg(), 2)];
        })->all();

        return ['weeks' => $weeks, 'series' => $series];
    }

    private function forms(string $type)
    {
        return DB::table('gmp_penilaian_form')->where('tipe_gmp', $type)->where('status', 'approved');
    }

    private function minScores(string $type)
    {
        return DB::table('gmp_penilaian_form as f')
            ->join('gmp_penilaian_form_detail as d', 'f.id', '=', 'd.form_id')
            ->where('f.tipe_gmp', $type)->where('f.status', 'approved');
    }
}
