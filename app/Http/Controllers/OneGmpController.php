<?php

namespace App\Http\Controllers;

use App\Services\GmpDashboardService;
use App\Services\GmpFindingService;
use App\Services\GmpMonitoringService;
use App\Support\MenuAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OneGmpController extends Controller
{
    private const QUESTION_MAPS = [
        'MHR' => [
            1 => ['section_key' => 'teknis', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 3 item penilaian dibawah', 'name' => 'Undulasi & Lubang', 'allowed_scores' => [0, 1, 2, 5]],
            2 => ['section_key' => 'teknis', 'title' => 'Grade jalan yang di lakukan penilaian', 'name' => 'Grade Jalan', 'allowed_scores' => [0, 1, 2, 5]],
            3 => ['section_key' => 'teknis', 'title' => 'Lebar Jalan Angkut yang dilakukan penilaian', 'name' => 'Lebar Jalan', 'allowed_scores' => [0, 1, 5]],
            4 => ['section_key' => 'teknis', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 3 item penilaian dibawah', 'name' => 'Superelevasi & Crossfall', 'allowed_scores' => [0, 1, 2, 5]],
            5 => ['section_key' => 'keselamatan', 'title' => 'Bundwall atau Tanggul Pengaman', 'name' => 'Bundwall', 'allowed_scores' => [0, 1, 2, 5]],
            6 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 4 item penilaian dibawah', 'name' => 'Drainase', 'allowed_scores' => [0, 1, 2, 5]],
            7 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Culvert', 'allowed_scores' => [0, 1, 2, 5]],
            8 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Divider & Median', 'allowed_scores' => [0, 1, 5]],
            9 => ['section_key' => 'keselamatan', 'title' => 'Sudut Persimpangan jalan', 'name' => 'Sudut Simpang', 'allowed_scores' => [0, 1, 5]],
            10 => ['section_key' => 'keselamatan', 'title' => 'Tumpukan Material Sisa (Spoil)', 'name' => 'Material Spoil', 'allowed_scores' => [0, 1, 2, 5]],
            11 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Rambu & Patok', 'allowed_scores' => [0, 1, 5]],
            12 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Debu & Blindspot', 'allowed_scores' => [0, 1, 5]],
        ],
        'MFOB' => [
            1 => ['section_key' => 'teknis', 'title' => 'Pilih nilai yang sesuai dengan Class Excavator', 'name' => 'Tinggi Jenjang', 'allowed_scores' => [0, 1, 3, 5]],
            2 => ['section_key' => 'teknis', 'title' => 'Pilih salah satu yang menggambarkan Luas Area Penggalian Front Loading', 'name' => 'Luasan Area', 'allowed_scores' => [0, 1, 3, 5]],
            3 => ['section_key' => 'teknis', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 3 item penilaian dibawah', 'name' => 'Grade Box', 'allowed_scores' => [0, 1, 2, 3, 4, 5]],
            4 => ['section_key' => 'teknis', 'title' => 'Pilih Kondisi Lantai Kerja', 'name' => 'Lantai Kerja', 'allowed_scores' => [0, 1, 5]],
            5 => ['section_key' => 'teknis', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 3 item penilaian dibawah', 'name' => 'Batas Penggalian', 'allowed_scores' => [0, 1, 3, 5]],
            6 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Lantai & Akses', 'allowed_scores' => [0, 1, 2, 3, 4, 5]],
            7 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Drainase & Air', 'allowed_scores' => [0, 1, 3, 5]],
            8 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 3 item penilaian dibawah', 'name' => 'Bundwall & Rambu', 'allowed_scores' => [0, 1, 3, 5]],
            9 => ['section_key' => 'keselamatan', 'title' => 'A. Perapihan Dinding Lereng Final Design', 'name' => 'Perapihan Dinding', 'allowed_scores' => [0, 5]],
            10 => ['section_key' => 'keselamatan', 'title' => 'Pengawasan Harian Area Kerja pada Awal Shift', 'name' => 'Pengawasan', 'allowed_scores' => [0, 1, 3, 5]],
            11 => ['section_key' => 'keselamatan', 'title' => 'Kondisi Tanggap Darurat', 'name' => 'Tanggap Darurat', 'allowed_scores' => [0, 1, 5]],
        ],
        'MFCO' => [
            1 => ['section_key' => 'teknis', 'title' => 'Pilih nilai yang sesuai dengan Class Excavator', 'name' => 'Tinggi Jenjang', 'allowed_scores' => [0, 1, 5]],
            2 => ['section_key' => 'teknis', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Metode Gali', 'allowed_scores' => [0, 1, 2, 3, 4, 5]],
            3 => ['section_key' => 'teknis', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 4 item penilaian dibawah', 'name' => 'Kebersihan Front', 'allowed_scores' => [0, 1, 3, 5]],
            4 => ['section_key' => 'teknis', 'title' => 'Pilihlah luasan area penggalian (Working Geometri)', 'name' => 'Luasan Area', 'allowed_scores' => [0, 1, 3, 5]],
            5 => ['section_key' => 'teknis', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 3 item penilaian dibawah', 'name' => 'Grade Box', 'allowed_scores' => [0, 1, 2, 3, 4, 5]],
            6 => ['section_key' => 'teknis', 'title' => 'Pilihlah Elevasi Lantai Kerja diarea front', 'name' => 'Lantai Kerja', 'allowed_scores' => [0, 1, 5]],
            7 => ['section_key' => 'teknis', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 3 item penilaian dibawah', 'name' => 'Batas Penggalian', 'allowed_scores' => [0, 1, 2, 4, 5]],
            8 => ['section_key' => 'teknis', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Label Coal Series', 'allowed_scores' => [0, 5]],
            9 => ['section_key' => 'teknis', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Kebersihan Unit', 'allowed_scores' => [0, 5]],
            10 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Lantai Penggalian', 'allowed_scores' => [0, 1, 2, 3, 4, 5]],
            11 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Drainase & Air', 'allowed_scores' => [0, 1, 3, 5]],
            12 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 4 item penilaian dibawah', 'name' => 'Bundwall', 'allowed_scores' => [0, 1, 3, 5]],
            13 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 3 item penilaian dibawah', 'name' => 'Pengawasan', 'allowed_scores' => [0, 1, 3, 5]],
            14 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 3 item penilaian dibawah', 'name' => 'Tanggap Darurat', 'allowed_scores' => [0, 1, 5]],
            15 => ['section_key' => 'optimalisasi', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Optimalisasi Floor', 'allowed_scores' => [0, 5]],
        ],
        'DISPOSAL' => [
            1 => ['section_key' => 'teknis', 'title' => 'Pilih kesesuaian unit Dozer dengan Alokasi Fleet Dumping', 'name' => 'Dozer Support', 'allowed_scores' => [0, 1, 3, 5]],
            2 => ['section_key' => 'teknis', 'title' => 'Pilih tinggi jenjang penimbunan diarea yang sedang di nilai', 'name' => 'Tinggi Jenjang', 'allowed_scores' => [0, 1, 3, 5]],
            3 => ['section_key' => 'teknis', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Lantai Timbun', 'allowed_scores' => [0, 1, 2, 3, 4, 5]],
            4 => ['section_key' => 'teknis', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Batas Timbun', 'allowed_scores' => [0, 1, 5]],
            5 => ['section_key' => 'teknis', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 3 item penilaian dibawah', 'name' => 'Grade Box', 'allowed_scores' => [0, 1, 2, 4, 5]],
            6 => ['section_key' => 'teknis', 'title' => 'Kondisi Lantai Kerja sesuai Elevasi atau tidak', 'name' => 'Elevasi Lantai', 'allowed_scores' => [0, 1, 5]],
            7 => ['section_key' => 'teknis', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 4 item penilaian dibawah', 'name' => 'Komposisi Dumping', 'allowed_scores' => [0, 1, 5]],
            8 => ['section_key' => 'teknis', 'title' => 'Material Penimbunan Disposal', 'name' => 'Material Timbun', 'allowed_scores' => [0, 1, 3, 5]],
            9 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 3 item penilaian dibawah', 'name' => 'Pos & Parkir', 'allowed_scores' => [0, 1, 5]],
            10 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai Lebar area penimbunan sesuai dengan unit HD yang Dumping', 'name' => 'Luasan Timbun', 'allowed_scores' => [0, 5]],
            11 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Drainase & Air', 'allowed_scores' => [0, 1, 3, 5]],
            12 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Bundwall', 'allowed_scores' => [0, 1, 2, 3, 4, 5]],
            13 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 3 item penilaian dibawah', 'name' => 'Pengawasan', 'allowed_scores' => [0, 2, 3, 5]],
            14 => ['section_key' => 'keselamatan', 'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah', 'name' => 'Rambu Disposal', 'allowed_scores' => [0, 1, 5]],
        ],
        'DEWATERING' => [
            1 => ['section_key' => 'teknis', 'title' => 'Kondisi Pipa Pompa Air Sump / HDPE', 'name' => 'Pipa HDPE', 'allowed_scores' => [0, 1, 2, 3, 4, 5]],
            2 => ['section_key' => 'teknis', 'title' => 'Kolam Penampung Sedimentasi (Sediment Trap) di Area Sump', 'name' => 'Sediment Trap', 'allowed_scores' => [0, 1, 2, 3, 4, 5]],
            3 => ['section_key' => 'teknis', 'title' => 'Kolam Control Box di Outlet Pompa Air Keluaran', 'name' => 'Control Box', 'allowed_scores' => [0, 1, 2, 3, 4, 5]],
            4 => ['section_key' => 'teknis', 'title' => 'Akses Menuju ke Pos Area Sump', 'name' => 'Akses Sump', 'allowed_scores' => [0, 1, 3, 5]],
            5 => ['section_key' => 'teknis', 'title' => 'Akses Sarana dan Prasarana ke Pompa', 'name' => 'Akses Pompa', 'allowed_scores' => [0, 1, 3, 5]],
            6 => ['section_key' => 'keselamatan', 'title' => 'Kondisi Elevasi Air Sump & Rambu Elevasi Air / Bar Meter di Sump', 'name' => 'Elevasi Air', 'allowed_scores' => [0, 1, 5]],
            7 => ['section_key' => 'keselamatan', 'title' => 'Kondisi Pompa Sump & Lampu Rotary pada Unit Pompa', 'name' => 'Kondisi Pompa', 'allowed_scores' => [0, 1, 5]],
            8 => ['section_key' => 'keselamatan', 'title' => '(Khusus Pompa Elektrik) Kabel Daya dari Trafo ke Pompa, Cable Tray/Ladder', 'name' => 'Pompa Elektrik', 'allowed_scores' => [0, 1, 5]],
            9 => ['section_key' => 'keselamatan', 'title' => 'Rambu-rambu di Area Sump', 'name' => 'Rambu Sump', 'allowed_scores' => [0, 1, 2, 3, 4, 5]],
            10 => ['section_key' => 'keselamatan', 'title' => 'Spill Kit di Area Sump', 'name' => 'Spillkit', 'allowed_scores' => [0, 1, 2, 3, 4, 5]],
            11 => ['section_key' => 'keselamatan', 'title' => 'Papan Informasi di Area Sump', 'name' => 'Papan Info', 'allowed_scores' => [0, 1, 2, 3, 4, 5]],
            12 => ['section_key' => 'keselamatan', 'title' => 'Logbook di Area Sump', 'name' => 'Logbook', 'allowed_scores' => [0, 1, 2, 3, 4, 5]],
            13 => ['section_key' => 'keselamatan', 'title' => 'Kondisi Tanggap Darurat', 'name' => 'Emergency Response', 'allowed_scores' => [0, 1, 5]],
            14 => ['section_key' => 'lingkungan', 'title' => 'Kebersihan di Area Sump / Housekeeping', 'name' => 'Housekeeping', 'allowed_scores' => [0, 1, 3, 5]],
            15 => ['section_key' => 'lingkungan', 'title' => 'Limbah / Sampah, Kebocoran / Tumpahan Hidrokarbon atau B3 & Peralatan Spill Kit', 'name' => 'B3 & Spill Kit', 'allowed_scores' => [0, 1, 2, 3, 4, 5]],
        ],
    ];

    private const TYPE_META = [
        'MHR' => ['name' => 'Mining Haul Road', 'accent' => '#059669', 'accent2' => '#064e3b'],
        'MFOB' => ['name' => 'Mining Face Overburden', 'accent' => '#f59e0b', 'accent2' => '#7c2d12'],
        'MFCO' => ['name' => 'Mining Face Coal Getting', 'accent' => '#7c3aed', 'accent2' => '#1e1b4b'],
        'DISPOSAL' => ['name' => 'Disposal Area', 'accent' => '#6366f1', 'accent2' => '#1e1b4b'],
        'DEWATERING' => ['name' => 'Dewatering System', 'accent' => '#06b6d4', 'accent2' => '#164e63'],
    ];

    /**
     * Dashboard GMP — alur & perhitungan sama dengan index.php?type=gmp v1
     * (Group Leader tetap melihat dashboard; operator/helper/trafficman tidak melihat grafik).
     */
    public function dashboard(Request $request, GmpDashboardService $dashboardService): Response
    {
        $type = $dashboardService->resolveType($request->query('gmp_type'));
        $year = (int) $request->query('tahun', now()->year);
        $availableWeeks = $dashboardService->availableWeeks($type, $year);

        // Default week = week terbaru yang punya data; nilai kosong = "Semua Week".
        $week = $request->has('week') ? (int) $request->query('week') : ($availableWeeks[0] ?? (int) now()->isoWeek());
        $weekLabel = $week > 0 ? $dashboardService->weekLabel($week, $year) : null;

        return Inertia::render('OneGmp/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_gmp_menu'),
            'filters' => ['gmp_type' => $type, 'week' => $week > 0 ? $week : '', 'tahun' => $year],
            'typeOptions' => collect(GmpDashboardService::TYPE_LABELS)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'weekOptions' => $availableWeeks,
            'yearOptions' => $dashboardService->yearOptions(),
            'canViewCharts' => ! in_array($request->user()?->role, ['operator', 'helper', 'trafficman'], true),
            ...$dashboardService->build($type, self::QUESTION_MAPS[$type], $year, $weekLabel, $availableWeeks),
        ]);
    }

    /**
     * Dashboard Monitoring GMP (Monitoring GMP v2).
     * Capaian Good Mining Practice: trend akumulasi, skor per aspek, dan skor per lokasi.
     */
    public function monitoring(Request $request, GmpMonitoringService $monitoringService): Response
    {
        $type = (string) $request->query('type', 'MHR');
        $year = $request->query('year');
        $week = $request->query('week');
        $aspekArea = (string) $request->query('aspek_area', $type);

        $data = $monitoringService->getData($type, $year, $week, $aspekArea);

        return Inertia::render('OneGmp/Monitoring', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_gmp_menu'),
            ...$data,
        ]);
    }

    /**
     * GMP Area — Tampilan GMP Area V1 dengan Hero Banner Hijau, Sub-Area Tabs (MHR/MFOB/MFCO/DISPOSAL/DEWATERING), Full-width Pareto, dan Grid Mini-Chart (Gambar 2).
     */
    public function area(Request $request, ?string $type = null): Response
    {
        if ($type) {
            $request->merge(['type' => $type]);
        }

        return Inertia::render('OneGmp/AreaIndex', $this->buildGmpEvaluationData($request, 'MHR'));
    }

    private function buildGmpEvaluationData(Request $request, string $defaultType = 'MHR'): array
    {
        $type = $this->resolveGmpType($request->input('type', $defaultType));
        $questions = self::QUESTION_MAPS[$type];

        $years = $this->gmpYears($type);
        $year = (string) $request->input('year', (string) ($years->first() ?? now()->format('Y')));

        $weeks = $this->gmpWeeks($type, $year);
        $week = (string) $request->input('week', (string) ($weeks->first() ?? ''));

        $logRows = $week !== '' ? $this->gmpLogRows($type, $week) : collect();

        [$pareto, $totalLocations] = $this->buildParetoData($questions, $logRows);
        [$rekapWeighted, $rekapRaw] = $this->buildRekapData($type, $questions, $logRows);
        $detailLog = $this->buildDetailLog($logRows);

        $overallScore = $totalLocations > 0 ? round(collect($rekapWeighted)->avg('total'), 1) : 0;

        return [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_gmp_menu'),
            'types' => collect(self::TYPE_META)->map(fn ($meta, $key) => array_merge(['key' => $key], $meta))->values(),
            'filters' => [
                'type' => $type,
                'year' => $year,
                'week' => $week,
            ],
            'years' => $years,
            'weeks' => $weeks,
            'questions' => collect($questions)->map(fn ($q, $no) => [
                'no' => $no,
                'name' => $q['name'],
                'section_key' => $q['section_key'],
                'weight' => config("gmp_scoring.weight_map.{$type}.{$no}", 0),
            ])->values(),
            'overallScore' => $overallScore,
            'totalLocations' => $totalLocations,
            'pareto' => $pareto,
            'rekapWeighted' => $rekapWeighted,
            'rekapRaw' => $rekapRaw,
            'rekapAverage' => $this->buildRekapAverage($type, $questions, $pareto, $rekapWeighted, $rekapRaw, $overallScore),
            'detailLog' => $detailLog,
        ];
    }

    private function resolveGmpType(?string $type): string
    {
        $type = strtoupper((string) $type);

        return array_key_exists($type, self::QUESTION_MAPS) ? $type : 'MHR';
    }

    private function gmpYears(string $type)
    {
        return DB::table('gmp_penilaian_form')
            ->where('tipe_gmp', $type)->where('status', 'approved')
            ->pluck('tanggal_gmp')
            ->map(fn ($date) => (int) substr($date, 0, 4))
            ->unique()
            ->sortDesc()
            ->values();
    }

    private function gmpWeeks(string $type, string $year)
    {
        return DB::table('gmp_penilaian_form')
            ->where('tipe_gmp', $type)->where('status', 'approved')
            ->whereYear('tanggal_gmp', $year)
            ->select('week', DB::raw('MAX(tanggal_gmp) as mx'))
            ->groupBy('week')
            ->orderByDesc('mx')
            ->pluck('week');
    }

    private function gmpLogRows(string $type, string $week)
    {
        return DB::table('gmp_penilaian_form as f')
            ->join('gmp_penilaian_form_detail as d', 'f.id', '=', 'd.form_id')
            ->where('f.tipe_gmp', $type)->where('f.week', $week)->where('f.status', 'approved')
            ->select('f.id', 'f.lokasi', 'f.penilai', 'f.tanggal_gmp', 'f.foto_area', 'f.total_percent', 'd.question_no', 'd.score_raw', 'd.foto_detail')
            ->orderBy('f.tanggal_gmp', 'desc')->orderBy('f.id', 'desc')->orderBy('d.question_no')
            ->get();
    }

    /** [question_no][lokasi] => skor raw terendah untuk kombinasi tsb. */
    private function minScorePerLokasi($logRows): array
    {
        $min = [];
        foreach ($logRows as $row) {
            $q = (int) $row->question_no;
            $raw = (int) $row->score_raw;
            if (! isset($min[$q][$row->lokasi]) || $raw < $min[$q][$row->lokasi]) {
                $min[$q][$row->lokasi] = $raw;
            }
        }

        return $min;
    }

    private function buildParetoData(array $questions, $logRows): array
    {
        $percentMap = config('gmp_scoring.percent_map');
        $minPerLokasi = $this->minScorePerLokasi($logRows);
        $totalLocations = $logRows->pluck('lokasi')->unique()->count();

        $pareto = [];
        foreach ($questions as $qIdx => $meta) {
            $sum = 0;
            foreach ($minPerLokasi[$qIdx] ?? [] as $raw) {
                $sum += $percentMap[$raw] ?? 0;
            }
            $pareto[] = [
                'no' => $qIdx,
                'label' => $meta['name'],
                'value' => $totalLocations > 0 ? round($sum / $totalLocations, 1) : 0,
            ];
        }

        // Kurva kumulatif: running sum dalam urutan kriteria (sesuai V1, bukan diurutkan berdasarkan nilai).
        $grandTotal = collect($pareto)->sum('value') ?: 1;
        $running = 0;
        foreach ($pareto as &$p) {
            $running += $p['value'];
            $p['cumulative'] = round($running / $grandTotal * 100, 1);
        }
        unset($p);

        return [$pareto, $totalLocations];
    }

    private function buildRekapData(string $type, array $questions, $logRows): array
    {
        $percentMap = config('gmp_scoring.percent_map');
        $weightMap = config('gmp_scoring.weight_map')[$type] ?? [];
        $minPerLokasi = $this->minScorePerLokasi($logRows);
        $locations = $logRows->pluck('lokasi')->unique()->values();

        $rekapWeighted = [];
        $rekapRaw = [];
        foreach ($locations as $lokasi) {
            $weightedScores = [];
            $rawScores = [];
            $percentScores = [];
            $totalWeighted = 0;
            $totalRaw = 0;
            foreach ($questions as $qIdx => $meta) {
                $raw = $minPerLokasi[$qIdx][$lokasi] ?? null;
                $rawScores[$qIdx] = $raw;
                $percentScores[$qIdx] = $raw !== null ? ($percentMap[$raw] ?? 0) : null;
                $totalRaw += $raw ?? 0;

                $weight = $weightMap[$qIdx] ?? 0;
                $weightedVal = $raw !== null ? round(($percentMap[$raw] ?? 0) * $weight / 100, 1) : null;
                $weightedScores[$qIdx] = $weightedVal;
                $totalWeighted += $weightedVal ?? 0;
            }
            $rekapWeighted[] = ['lokasi' => $lokasi, 'scores' => $weightedScores, 'percentScores' => $percentScores, 'total' => round($totalWeighted, 1)];
            $rekapRaw[] = ['lokasi' => $lokasi, 'scores' => $rawScores, 'percentScores' => $percentScores, 'total' => $totalRaw];
        }

        usort($rekapWeighted, fn ($a, $b) => $a['total'] <=> $b['total']);
        usort($rekapRaw, fn ($a, $b) => $a['total'] <=> $b['total']);

        return [$rekapWeighted, $rekapRaw];
    }

    /**
     * Baris "RATA-RATA" di rekap tabel. Untuk view weighted, nilai per kolom Q memakai
     * rata-rata performa (%) yang SAMA dengan chart Pareto (bukan rata-rata nilai terbobot
     * itu sendiri) — persis perilaku V1. Untuk view raw, nilai per kolom = rata-rata skor mentah.
     */
    private function buildRekapAverage(string $type, array $questions, array $pareto, array $rekapWeighted, array $rekapRaw, float $overallScore): array
    {
        $weightMap = config('gmp_scoring.weight_map')[$type] ?? [];

        $weightedScores = [];
        $rawScores = [];
        $weightedPercents = [];
        foreach ($questions as $qIdx => $meta) {
            $weight = $weightMap[$qIdx] ?? 0;

            // HASIL ITU BANG RATA RATA POIN KALI 100 BGI BOBOT
            $locPoints = array_filter(array_map(fn ($r) => $r['scores'][$qIdx] ?? null, $rekapWeighted), fn ($v) => $v !== null);
            $avgPoint = count($locPoints) > 0 ? (array_sum($locPoints) / count($locPoints)) : 0;
            $calculatedValue = $weight > 0 ? round(($avgPoint * 100) / $weight, 1) : 0;

            $weightedScores[$qIdx] = $calculatedValue;
            $weightedPercents[$qIdx] = $calculatedValue;

            $values = array_filter(array_map(fn ($r) => $r['scores'][$qIdx] ?? null, $rekapRaw), fn ($v) => $v !== null);
            $rawScores[$qIdx] = $values !== [] ? round(array_sum($values) / count($values), 2) : 0;
        }

        return [
            'weighted' => [
                'scores' => $weightedScores,
                'percentScores' => $weightedPercents,
                'total' => $overallScore,
            ],
            'raw' => [
                'scores' => $rawScores,
                'total' => count($rekapRaw) > 0 ? round(collect($rekapRaw)->avg('total'), 2) : 0,
            ],
        ];
    }

    private function buildDetailLog($logRows): array
    {
        $forms = [];
        foreach ($logRows as $row) {
            $fid = $row->id;
            if (! isset($forms[$fid])) {
                $forms[$fid] = [
                    'id' => $fid,
                    'lokasi' => $row->lokasi,
                    'penilai' => $row->penilai,
                    'tanggal' => $row->tanggal_gmp,
                    'foto_area' => $row->foto_area ?? null,
                    'total_percent' => round((float) $row->total_percent, 1),
                    'scores' => [],
                    'question_photos' => [],
                ];
            }
            $forms[$fid]['scores'][(int) $row->question_no] = (int) $row->score_raw;
            if (!empty($row->foto_detail)) {
                $forms[$fid]['question_photos'][(int) $row->question_no] = $row->foto_detail;
            }
        }

        return array_values($forms);
    }

    /**
     * Trend GMP — Analytics & Weekly Comparison
     */
    public function trend(Request $request, GmpDashboardService $dashboardService): Response
    {
        $year = (string) $request->input('year', now()->format('Y'));
        $type = strtoupper((string) $request->input('type', 'MHR'));

        $weeklyTrend = DB::table('gmp_penilaian_form')
            ->select('week', 'tipe_gmp', DB::raw('AVG(total_percent) as avg_percent'))
            ->whereYear('tanggal_gmp', $year)
            ->where('status', 'approved')
            ->groupBy('week', 'tipe_gmp')
            ->orderBy('week', 'asc')
            ->get();

        return Inertia::render('OneGmp/Trend', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_gmp_menu'),
            'filters' => [
                'year' => $year,
                'type' => $type,
            ],
            'weeklyTrend' => $weeklyTrend,
            'trend' => $dashboardService->weeklyTrend((int) $year),
            'yearOptions' => $dashboardService->yearOptions(),
        ]);
    }

    /**
     * Penilaian GMP — Questionnaire Form Page
     */
    public function penilaian(Request $request, string $type): Response
    {
        $type = strtoupper($type);
        $questions = self::QUESTION_MAPS[$type] ?? self::QUESTION_MAPS['MHR'];

        $masterLocations = DB::table('gmp_master_lokasi')
            ->where('tipe_gmp', $type)
            ->pluck('nama_lokasi')
            ->all();

        return Inertia::render('OneGmp/PenilaianForm', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_gmp_menu'),
            'type' => $type,
            'questions' => $questions,
            'rubrics' => config("gmp_rubrics.{$type}", []),
            'masterLocations' => $masterLocations,
        ]);
    }

    /**
     * Store Evaluation Form & Calculate Weighted Score
     */
    public function storePenilaian(Request $request)
    {
        $validated = $request->validate([
            'tipe_gmp' => 'required|string',
            'lokasi' => 'required|string',
            'penilai' => 'required|string',
            'nrp' => 'required|string',
            'tanggal_gmp' => 'required|date',
            'keterangan' => 'nullable|string',
            'foto_area' => 'nullable',
            'question_photos' => 'nullable',
            'answers' => 'required|array',
            'editedQuestions' => 'nullable|array',
            'editedRubrics' => 'nullable|array',
        ]);

        $type = strtoupper($validated['tipe_gmp']);
        $configScoring = config('gmp_scoring');
        $percentMap = $configScoring['percent_map'] ?? [];
        $weightMap = $configScoring['weight_map'][$type] ?? [];
        $questionMap = self::QUESTION_MAPS[$type] ?? [];

        // Upload foto area jika disertakan
        $fotoPath = null;
        if ($request->hasFile('foto_area')) {
            $path = $request->file('foto_area')->store('uploads/gmp', 'public');
            $fotoPath = '/storage/' . $path;
        } elseif (is_string($request->input('foto_area')) && !empty($request->input('foto_area'))) {
            $fotoPath = $request->input('foto_area');
        }

        // Merge edited questions ke questionMap jika ada
        if (!empty($validated['editedQuestions'])) {
            foreach ($validated['editedQuestions'] as $qNo => $editedData) {
                if (isset($questionMap[$qNo])) {
                    $questionMap[$qNo] = array_merge($questionMap[$qNo], $editedData);
                }
            }
        }

        $totalRaw = 0;
        $totalWeighted = 0;
        $detailsData = [];
        $questionFiles = $request->file('question_photos') ?? [];

        foreach ($validated['answers'] as $qNo => $scoreRaw) {
            $scoreRaw = (int) $scoreRaw;
            $meta = $questionMap[$qNo] ?? ['section_key' => 'teknis', 'title' => 'Kriteria', 'name' => 'Soal ' . $qNo];
            $basePct = $percentMap[$scoreRaw] ?? 0;
            $weight = $weightMap[$qNo] ?? 0;
            $weightedVal = ($basePct * $weight / 100);

            $totalRaw += $scoreRaw;
            $totalWeighted += $weightedVal;

            // Upload foto per-soal jika ada
            $fotoDetail = null;
            if ($request->hasFile("question_photos.{$qNo}")) {
                $path = $request->file("question_photos.{$qNo}")->store('uploads/gmp/questions', 'public');
                $fotoDetail = '/storage/' . $path;
            } elseif (isset($questionFiles[$qNo]) && $questionFiles[$qNo] instanceof \Illuminate\Http\UploadedFile) {
                $path = $questionFiles[$qNo]->store('uploads/gmp/questions', 'public');
                $fotoDetail = '/storage/' . $path;
            } elseif (is_string($request->input("question_photos.{$qNo}")) && !empty($request->input("question_photos.{$qNo}"))) {
                $fotoDetail = $request->input("question_photos.{$qNo}");
            }

            $detailsData[] = [
                'question_no' => $qNo,
                'section_key' => $meta['section_key'],
                'criterion_title' => $meta['title'],
                'criterion_name' => $meta['name'],
                'score_raw' => $scoreRaw,
                'score_percent' => $basePct,
                'foto_detail' => $fotoDetail,
            ];
        }

        $dt = new \DateTime($validated['tanggal_gmp']);
        $weekLabel = 'Week ' . $dt->format('W') . ' - ' . $dt->format('o');

        $formId = DB::table('gmp_penilaian_form')->insertGetId([
            'tipe_gmp' => $type,
            'week' => $weekLabel,
            'lokasi' => $validated['lokasi'],
            'penilai' => $validated['penilai'],
            'nrp' => $validated['nrp'],
            'tanggal_gmp' => $validated['tanggal_gmp'],
            'foto_area' => $fotoPath,
            'total_score' => $totalRaw,
            'total_percent' => round($totalWeighted, 2),
            'question_count' => count($detailsData),
            'keterangan' => $validated['keterangan'] ?? null,
            'created_by' => auth()->user()->username ?? 'system',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($detailsData as &$detail) {
            $detail['form_id'] = $formId;
            $detail['created_at'] = now();
            $detail['updated_at'] = now();
        }
        DB::table('gmp_penilaian_form_detail')->insert($detailsData);

        return redirect()->route('one-gmp.area', ['type' => $type])
            ->with('success', 'Penilaian GMP berhasil dikirim dan menunggu approval.');
    }

    /**
     * Hapus 1 record penilaian GMP (dipanggil dari tabel Data Detail Penilaian di Dashboard GMP).
     * Detail jawaban ikut terhapus otomatis lewat cascadeOnDelete di migration.
     */
    public function destroyPenilaian($id)
    {
        DB::table('gmp_penilaian_form')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Data penilaian GMP berhasil dihapus.');
    }

    /**
     * Approval GMP — List of Pending Assessments
     */
    public function approval(Request $request): Response
    {
        $pendingForms = DB::table('gmp_penilaian_form')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('OneGmp/Approval', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_gmp_menu'),
            'pendingForms' => $pendingForms,
        ]);
    }

    public function approveForm($id, GmpFindingService $findingService)
    {
        $form = DB::table('gmp_penilaian_form')->where('id', $id)->first();
        if (! $form) return redirect()->back()->with('error', 'Form penilaian tidak ditemukan.');

        $draftCount = DB::transaction(function () use ($id, $form, $findingService) {
            DB::table('gmp_penilaian_form')->where('id', $id)->update([
                'status' => 'approved',
                'approved_by' => auth()->user()->name ?? 'Approver',
                'approved_at' => now(),
                'updated_at' => now(),
            ]);

            // Draft temuan otomatis untuk setiap item bernilai 0 (sama seperti v1)
            return $findingService->createDraftsFromAssessment($form, (string) (auth()->user()->username ?? 'Sistem'));
        });

        return redirect()->back()->with('success', 'Penilaian berhasil disetujui'.($draftCount > 0 ? " dan {$draftCount} draft temuan otomatis dibuat." : '.'));
    }

    public function rejectForm(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_note' => 'required|string',
        ]);

        DB::table('gmp_penilaian_form')->where('id', $id)->update([
            'status' => 'rejected',
            'rejection_note' => $validated['rejection_note'],
            'approved_by' => auth()->user()->name ?? 'Approver',
            'approved_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Form penilaian berhasil ditolak.');
    }

    public function returnForm(Request $request, $id)
    {
        $validated = $request->validate([
            'revision_note' => 'required|string',
        ]);

        DB::table('gmp_penilaian_form')->where('id', $id)->update([
            'status' => 'returned',
            'revision_note' => $validated['revision_note'],
            'approved_by' => auth()->user()->name ?? 'Approver',
            'approved_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Form penilaian dikembalikan untuk revisi.');
    }

    /**
     * Ubah bobot satu pertanyaan GMP (dari halaman GMP Area). Bobot disimpan di
     * config/gmp_scoring.php (weight_map) agar tetap jadi sumber tunggal perhitungan skor.
     */
    public function updateQuestionWeight(Request $request, int $no)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'weight' => 'required|integer|min:0|max:100',
        ]);

        $type = $this->resolveGmpType($validated['type']);
        abort_unless(isset(self::QUESTION_MAPS[$type][$no]), 404);

        $path = config_path('gmp_scoring.php');
        $content = file_get_contents($path);
        $updated = preg_replace_callback(
            "/('{$type}' => \[)(.*?)(\])/s",
            fn ($m) => $m[1].preg_replace('/\b'.$no.' => \d+/', $no.' => '.(int) $validated['weight'], $m[2], 1).$m[3],
            $content,
            1
        );
        abort_if($updated === null, 500, 'Gagal memperbarui bobot pertanyaan.');

        file_put_contents($path, $updated);
        config(["gmp_scoring.weight_map.{$type}.{$no}" => (int) $validated['weight']]);

        return redirect()->back()->with('success', 'Bobot pertanyaan berhasil diperbarui.');
    }

    /**
     * Master GMP — Master Location Data per Area
     */
    public function masterGmp(Request $request): Response
    {
        $type = strtoupper((string) $request->input('type', 'MHR'));

        $locations = DB::table('gmp_master_lokasi')
            ->where('tipe_gmp', $type)
            ->orderBy('nama_lokasi', 'asc')
            ->get();

        return Inertia::render('OneGmp/MasterGmp', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_gmp_menu'),
            'type' => $type,
            'locations' => $locations,
        ]);
    }

    public function storeMaster(Request $request)
    {
        $validated = $request->validate([
            'tipe_gmp' => 'required|string',
            'nama_lokasi' => 'required|string',
        ]);

        DB::table('gmp_master_lokasi')->insert([
            'tipe_gmp' => strtoupper($validated['tipe_gmp']),
            'nama_lokasi' => trim($validated['nama_lokasi']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Master lokasi berhasil ditambahkan.');
    }

    public function deleteMaster($id)
    {
        DB::table('gmp_master_lokasi')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Master lokasi berhasil dihapus.');
    }
}
