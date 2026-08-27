<?php

namespace App\Http\Controllers;

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

    /**
     * Dashboard GMP — Metrics & High Level Overview
     */
    public function dashboard(Request $request): Response
    {
        $year = (string) $request->input('year', now()->format('Y'));
        $type = strtoupper((string) $request->input('type', 'MHR'));

        $totalPenilaian = DB::table('gmp_penilaian_form')
            ->whereYear('tanggal_gmp', $year)
            ->where('status', 'approved')
            ->count();

        $avgScore = DB::table('gmp_penilaian_form')
            ->whereYear('tanggal_gmp', $year)
            ->where('status', 'approved')
            ->avg('total_percent') ?? 0;

        $uniqueLocations = DB::table('gmp_penilaian_form')
            ->whereYear('tanggal_gmp', $year)
            ->where('status', 'approved')
            ->distinct('lokasi')
            ->count('lokasi');

        $criticalAreas = DB::table('gmp_penilaian_form')
            ->select('lokasi', DB::raw('AVG(total_percent) as avg_pct'))
            ->whereYear('tanggal_gmp', $year)
            ->where('status', 'approved')
            ->groupBy('lokasi')
            ->having('avg_pct', '<', 50)
            ->get()
            ->count();

        $areaStats = DB::table('gmp_penilaian_form')
            ->select('tipe_gmp', DB::raw('COUNT(*) as total'), DB::raw('AVG(total_percent) as avg_percent'))
            ->whereYear('tanggal_gmp', $year)
            ->where('status', 'approved')
            ->groupBy('tipe_gmp')
            ->get();

        return Inertia::render('OneGmp/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_gmp_menu'),
            'filters' => [
                'year' => $year,
                'type' => $type,
            ],
            'metrics' => [
                'total_penilaian' => $totalPenilaian,
                'avg_score' => round($avgScore, 1),
                'jumlah_lokasi' => $uniqueLocations,
                'critical_areas' => $criticalAreas,
            ],
            'areaStats' => $areaStats,
        ]);
    }

    /**
     * Trend GMP — Analytics & Weekly Comparison
     */
    public function trend(Request $request): Response
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
        ]);
    }

    /**
     * Area GMP — Sub-area forms/cards (MHR, MFOB, MFOC, DISPOSAL, DEWATERING)
     */
    public function area(Request $request, string $type): Response
    {
        $type = strtoupper($type);
        $search = trim((string) $request->input('q', ''));

        $query = DB::table('gmp_penilaian_form')
            ->where('tipe_gmp', $type);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('lokasi', 'like', "%{$search}%")
                  ->orWhere('penilai', 'like', "%{$search}%")
                  ->orWhere('nrp', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy('tanggal_gmp', 'desc')->paginate(20);

        return Inertia::render('OneGmp/AreaIndex', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_gmp_menu'),
            'type' => $type,
            'filters' => ['search' => $search],
            'records' => $records,
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
            'foto_area' => 'nullable|string',
            'answers' => 'required|array',
        ]);

        $type = strtoupper($validated['tipe_gmp']);
        $configScoring = config('gmp_scoring');
        $percentMap = $configScoring['percent_map'] ?? [];
        $weightMap = $configScoring['weight_map'][$type] ?? [];
        $questionMap = self::QUESTION_MAPS[$type] ?? [];

        $totalRaw = 0;
        $totalWeighted = 0;
        $detailsData = [];

        foreach ($validated['answers'] as $qNo => $scoreRaw) {
            $scoreRaw = (int) $scoreRaw;
            $meta = $questionMap[$qNo] ?? ['section_key' => 'teknis', 'title' => 'Kriteria', 'name' => 'Soal ' . $qNo];
            $basePct = $percentMap[$scoreRaw] ?? 0;
            $weight = $weightMap[$qNo] ?? 0;
            $weightedVal = ($basePct * $weight / 100);

            $totalRaw += $scoreRaw;
            $totalWeighted += $weightedVal;

            $detailsData[] = [
                'question_no' => $qNo,
                'section_key' => $meta['section_key'],
                'criterion_title' => $meta['title'],
                'criterion_name' => $meta['name'],
                'score_raw' => $scoreRaw,
                'score_percent' => $basePct,
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
            'foto_area' => $validated['foto_area'] ?? null,
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

    public function approveForm($id)
    {
        $form = DB::table('gmp_penilaian_form')->where('id', $id)->first();
        if ($form) {
            DB::table('gmp_penilaian_form')->where('id', $id)->update([
                'status' => 'approved',
                'approved_by' => auth()->user()->name ?? 'Approver',
                'approved_at' => now(),
                'updated_at' => now(),
            ]);

            // Auto-Draft Temuan for raw score = 0
            $badDetails = DB::table('gmp_penilaian_form_detail')
                ->where('form_id', $id)
                ->where('score_raw', 0)
                ->get();

            foreach ($badDetails as $d) {
                $maxId = DB::table('gmp_temuan')->max('id') + 1;
                $kode = 'GMP-FND-' . date('Y') . '-' . str_pad($maxId, 4, '0', STR_PAD_LEFT);

                $temuanId = DB::table('gmp_temuan')->insertGetId([
                    'finding_code' => $kode,
                    'source_type' => 'Penilaian GMP',
                    'assessment_id' => $id,
                    'assessment_detail_id' => $d->question_no,
                    'year' => (int) date('Y'),
                    'week' => $form->week,
                    'area_id' => $form->tipe_gmp,
                    'location_id' => $form->lokasi,
                    'aspect_id' => strtoupper($d->section_key),
                    'checklist_id' => $d->criterion_name,
                    'standard_description' => 'Belum sesuai standar',
                    'finding_description' => 'Temuan dari Penilaian GMP - ' . $d->criterion_title,
                    'finding_date' => $form->tanggal_gmp,
                    'created_by' => auth()->user()->username ?? 'system',
                    'status' => 'draft',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($form->foto_area) {
                    DB::table('gmp_temuan_evidence')->insert([
                        'finding_id' => $temuanId,
                        'evidence_type' => 'before',
                        'file_path' => $form->foto_area,
                        'uploaded_by' => auth()->user()->username ?? 'system',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Form penilaian berhasil disetujui.');
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
