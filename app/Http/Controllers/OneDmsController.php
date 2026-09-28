<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIzinFatigueRequest;
use App\Support\MenuAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OneDmsController extends Controller
{
    private const JENIS_DEVIASI = [
        'Driver Fatigue', 'Izin Fatigue', 'Driver Distraction', 'Phone Detection', 'Smoking Detection',
        'Yawning Detection', 'Eye Blink', 'Camera Covering', 'Forward Collision',
        'Video Loss Alarm', 'No Driver', 'Abnormal Speed Indication',
    ];

    private const ADAS_MAP = [
        'izin-fatigue' => 'Izin Fatigue',
        'driver-distraction' => 'Driver Distraction',
        'phone-detection' => 'Phone Detection',
        'yawning-detection' => 'Yawning Detection',
        'eye-blink' => 'Eye Blink',
        'camera-covering' => 'Camera Covering',
        'forward-collision' => 'Forward Collision',
        'video-loss-alarm' => 'Video Loss Alarm',
        'no-driver' => 'No Driver',
        'abnormal-speed' => 'Abnormal Speed Indication',
    ];

    private const TEMUAN_OPTIONS = ['NOTIF', 'LIVE'];
    private const NOTE_OPTIONS = [
        'Intercom',
        'Validasi Lapangan (Tatap Muka)',
        'Validasi Lapangan (Radio)',
        'Validasi Medic',
        'Radio RIG',
        'Progress Plan',
        'Validasi Manpower'
    ];
    private const STATUS_OPTIONS = ['Open', 'In Progress', 'Closed'];

    /**
     * Union semua sumber data deviasi DMS/ICC (dipakai dashboard & rekap).
     */
    private function unifiedDeviasiQuery()
    {
        // icc_dms_data yang berstatus Closed di-mirror ke fatigue_data/fatigue_adas_alarm/smoking_deviasi
        // (lihat syncToDataIcc). Baris yang sudah punya synced_*_id dikecualikan di sini supaya
        // kejadian yang sama tidak ke-hitung dobel lewat union.
        $q1 = DB::table('icc_dms_data')
            ->whereNull('synced_adas_id')
            ->whereNull('synced_fatigue_id')
            ->whereNull('synced_smoking_id')
            ->select('tanggal', 'shift', 'nrp', 'nama_operator', 'jenis_deviasi', 'deviasi_ke', 'status', 'waktu');
        $q2 = DB::table('fatigue_data')->select('tanggal', 'shift', 'nrp', 'nama_operator', 'jenis_deviasi', 'deviasi_ke', 'status', 'waktu');
        $q3 = DB::table('fatigue_adas_alarm')->select('tanggal', 'shift', 'nrp', 'nama_operator', 'jenis_deviasi', 'deviasi_ke', 'status', 'waktu');
        $q4 = DB::table('smoking_deviasi')->select('tanggal', 'shift', 'nrp', 'nama_operator', DB::raw("'Smoking Detection' as jenis_deviasi"), DB::raw("1 as deviasi_ke"), 'status', DB::raw('NULL as waktu'));

        $unifiedQuery = $q1->unionAll($q2)->unionAll($q3)->unionAll($q4);

        return DB::query()->fromSub($unifiedQuery, 'dms_all');
    }

    /**
     * Normalisasi format shift yang tidak konsisten antar tabel ('1'/'2' vs 'Shift 1'/'Shift 2').
     */
    private function whereShiftGroup($query, string $shift)
    {
        if ($shift === '') {
            return $query;
        }

        return $query->whereRaw("(CASE WHEN shift LIKE '%2%' THEN '2' ELSE '1' END) = ?", [$shift]);
    }

    /**
     * Tempelkan status pemanggilan (jumlah & tanggal terakhir) ke tiap baris operator, dicocokkan lewat NRP.
     */
    private function withPemanggilanStatus($operators)
    {
        $nrps = $operators->pluck('nrp')->filter()->unique()->values()->all();

        $pemanggilanByNrp = empty($nrps)
            ? collect()
            : DB::table('pemanggilan_karyawans')
                ->whereIn('nrp', $nrps)
                ->select('nrp', DB::raw('COUNT(*) as jumlah'), DB::raw('MAX(tanggal_pemanggilan) as terakhir'))
                ->groupBy('nrp')
                ->get()
                ->keyBy('nrp');

        return $operators->map(function ($op) use ($pemanggilanByNrp) {
            $p = $pemanggilanByNrp->get($op->nrp);
            $op->pemanggilanJumlah = $p->jumlah ?? 0;
            $op->pemanggilanTerakhir = $p->terakhir ?? null;
            return $op;
        });
    }

    /**
     * Tanggal & shift yang lagi berjalan berdasarkan jam saat ini.
     * Shift 1: 06:30–18:29. Shift 2: 18:30–06:29 (melewati tengah malam, tapi tanggalnya
     * tetap tanggal mulai shift, bukan tanggal kalender saat ini).
     */
    private function currentShiftContext(): array
    {
        // Jam pergantian shift (06:30 / 18:30) mengacu ke waktu lokal WIB, bukan UTC (default app.timezone).
        $now = now('Asia/Jakarta');
        $time = $now->format('H:i:s');

        if ($time >= '06:30:00' && $time < '18:30:00') {
            return [$now->format('Y-m-d'), '1'];
        }

        if ($time >= '18:30:00') {
            return [$now->format('Y-m-d'), '2'];
        }

        return [$now->copy()->subDay()->format('Y-m-d'), '2'];
    }

    /**
     * Dashboard DMS — Grafik Statistik & Metric Analytics
     */
    public function dashboard(Request $request): Response
    {
        $bulan = (string) $request->input('bulan', now()->format('m'));
        $tahun = (string) $request->input('tahun', now()->format('Y'));
        $shift = (string) $request->input('shift', '');

        $query = $this->unifiedDeviasiQuery()
            ->whereYear('tanggal', $tahun);

        if ($bulan !== '') {
            $query->whereMonth('tanggal', $bulan);
        }
        $query = $this->whereShiftGroup($query, $shift);

        $totalCases = (clone $query)->count();
        $uniqueOperators = (clone $query)->whereNotNull('nrp')->where('nrp', '<>', '')->distinct('nrp')->count('nrp');
        $closedCases = (clone $query)->where('status', 'Closed')->count();
        $deviasi1 = (clone $query)->where('deviasi_ke', 1)->count();
        $deviasi2 = (clone $query)->where('deviasi_ke', '>=', 2)->count();

        // Tally per Jenis Deviasi
        $tallyRaw = (clone $query)
            ->select('jenis_deviasi', DB::raw('COUNT(*) as total'))
            ->groupBy('jenis_deviasi')
            ->pluck('total', 'jenis_deviasi')
            ->all();

        $tally = array_fill_keys(self::JENIS_DEVIASI, 0);
        foreach ($tallyRaw as $key => $cnt) {
            if (isset($tally[$key])) {
                $tally[$key] = $cnt;
            }
        }

        // Daily Trend
        $dailyTrend = (clone $query)
            ->select('tanggal', DB::raw('COUNT(*) as total'))
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        // Monthly Trend
        $monthlyTrend = (clone $query)
            ->select(DB::raw('MONTH(tanggal) as bulan'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('MONTH(tanggal)'))
            ->orderBy('bulan', 'asc')
            ->get();

        // Jam Paling Sering Fatigue (Driver Fatigue, per jam 00:00-23:00)
        $jamFatigueRaw = (clone $query)
            ->where('jenis_deviasi', 'Driver Fatigue')
            ->whereNotNull('waktu')
            ->where('waktu', '<>', '')
            ->select(DB::raw('HOUR(waktu) as jam'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('HOUR(waktu)'))
            ->pluck('total', 'jam')
            ->all();

        $jamFatigue = [];
        for ($jam = 0; $jam < 24; $jam++) {
            $jamFatigue[] = [
                'jam' => sprintf('%02d:00', $jam),
                'total' => $jamFatigueRaw[$jam] ?? 0,
            ];
        }

        // Tren Izin Fatigue (per bulan, tahun berjalan, ikut filter bulan & shift yang aktif)
        $izinFatigueQuery = $this->whereShiftGroup(
            DB::table('izin_fatigue_data')->whereYear('tanggal', $tahun),
            $shift
        );
        if ($bulan !== '') {
            $izinFatigueQuery->whereMonth('tanggal', $bulan);
        }

        $izinFatigueMonthlyRaw = (clone $izinFatigueQuery)
            ->select(DB::raw('MONTH(tanggal) as bulan'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('MONTH(tanggal)'))
            ->pluck('total', 'bulan')
            ->all();

        $izinFatigueTrend = [];
        for ($m = 1; $m <= 12; $m++) {
            $izinFatigueTrend[] = ['bulan' => $m, 'total' => $izinFatigueMonthlyRaw[$m] ?? 0];
        }
        $izinFatigueTotal = array_sum($izinFatigueMonthlyRaw);
        // Izin Fatigue sumbernya tabel terpisah (izin_fatigue_data), tidak ikut union unifiedDeviasiQuery.
        $tally['Izin Fatigue'] = $izinFatigueTotal;

        // Top 10 Operator Izin Fatigue
        $topIzinFatigueOperators = $this->withPemanggilanStatus(
            (clone $izinFatigueQuery)
                ->select('nama_operator', 'nrp', DB::raw('COUNT(*) as total_cases'))
                ->whereNotNull('nama_operator')
                ->where('nama_operator', '<>', '')
                ->groupBy('nama_operator', 'nrp')
                ->orderByDesc('total_cases')
                ->limit(10)
                ->get()
        );

        // Rekapitulasi Deviasi per Shift (matrix jenis deviasi x tanggal, bulan berjalan)
        // Izin Fatigue dikecualikan karena sumber datanya terpisah (tabel izin_fatigue_data), lihat izinFatigueTrend.
        $rekapJenisList = array_values(array_diff(self::JENIS_DEVIASI, ['Izin Fatigue']));

        $rekapBulan = $bulan !== '' ? $bulan : now()->format('m');
        $rekapDaysInMonth = (int) \Carbon\Carbon::createFromDate((int) $tahun, (int) $rekapBulan, 1)->daysInMonth;

        $rekapRaw = $this->unifiedDeviasiQuery()
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $rekapBulan)
            ->select(
                DB::raw("CASE WHEN shift LIKE '%2%' THEN '2' ELSE '1' END as shift_group"),
                'jenis_deviasi',
                DB::raw('DAY(tanggal) as hari'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('shift_group', 'jenis_deviasi', DB::raw('DAY(tanggal)'))
            ->get();

        $rekapPerShift = [
            '1' => array_fill_keys($rekapJenisList, array_fill(1, $rekapDaysInMonth, 0)),
            '2' => array_fill_keys($rekapJenisList, array_fill(1, $rekapDaysInMonth, 0)),
        ];
        foreach ($rekapRaw as $row) {
            if (isset($rekapPerShift[$row->shift_group][$row->jenis_deviasi][$row->hari])) {
                $rekapPerShift[$row->shift_group][$row->jenis_deviasi][$row->hari] = $row->total;
            }
        }

        // Top 10 Operators
        $topOperators = $this->withPemanggilanStatus(
            (clone $query)
                ->select('nama_operator', 'nrp', DB::raw('COUNT(*) as total_cases'))
                ->whereNotNull('nama_operator')
                ->where('nama_operator', '<>', '')
                ->groupBy('nama_operator', 'nrp')
                ->orderByDesc('total_cases')
                ->limit(10)
                ->get()
        );

        return Inertia::render('OneDms/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_dms_menu'),
            'filters' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'shift' => $shift,
            ],
            'stats' => [
                'total_cases' => $totalCases,
                'unique_operators' => $uniqueOperators,
                'closed_cases' => $closedCases,
                'deviasi_1' => $deviasi1,
                'deviasi_2' => $deviasi2,
            ],
            'tally' => $tally,
            'dailyTrend' => $dailyTrend,
            'monthlyTrend' => $monthlyTrend,
            'jamFatigue' => $jamFatigue,
            'izinFatigueTrend' => $izinFatigueTrend,
            'izinFatigueTotal' => $izinFatigueTotal,
            'topIzinFatigueOperators' => $topIzinFatigueOperators,
            'rekapPerShift' => $rekapPerShift,
            'rekapBulan' => (int) $rekapBulan,
            'rekapDaysInMonth' => $rekapDaysInMonth,
            'rekapJenisList' => $rekapJenisList,
            'topOperators' => $topOperators,
        ]);
    }

    /**
     * Setting global dokumen Formulir DMS (No Dokumen, Revisi, dst) — satu baris, berlaku untuk semua tanggal/shift.
     */
    private function formSettings(): array
    {
        $row = DB::table('one_dms_form_settings')->first();

        if (!$row) {
            $id = DB::table('one_dms_form_settings')->insertGetId([
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $row = DB::table('one_dms_form_settings')->where('id', $id)->first();
        }

        return [
            'docNo' => $row->doc_no,
            'docRevisi' => $row->doc_revisi,
            'docTglEfektif' => $row->doc_tgl_efektif,
            'docHalaman' => $row->doc_halaman,
            'tallyTitle' => $row->tally_title,
        ];
    }

    /**
     * Simpan perubahan setting dokumen Formulir DMS (auto-save saat user selesai edit field).
     */
    public function saveFormSettings(Request $request)
    {
        $validated = $request->validate([
            'docNo' => 'nullable|string|max:255',
            'docRevisi' => 'nullable|string|max:255',
            'docTglEfektif' => 'nullable|string|max:255',
            'docHalaman' => 'nullable|string|max:255',
            'tallyTitle' => 'nullable|string|max:255',
        ]);

        // Partial update: cuma simpan field yang dikirim di request ini, field lain (dari
        // komponen lain yang gak ikut kirim) gak boleh ke-timpa jadi kosong.
        $columnMap = [
            'docNo' => 'doc_no',
            'docRevisi' => 'doc_revisi',
            'docTglEfektif' => 'doc_tgl_efektif',
            'docHalaman' => 'doc_halaman',
            'tallyTitle' => 'tally_title',
        ];

        $data = [];
        foreach ($columnMap as $key => $column) {
            if ($request->has($key)) {
                $data[$column] = $validated[$key] ?? '';
            }
        }

        $row = DB::table('one_dms_form_settings')->first();
        if ($row) {
            DB::table('one_dms_form_settings')->where('id', $row->id)->update($data + ['updated_at' => now()]);
        } else {
            DB::table('one_dms_form_settings')->insert($data + ['created_at' => now(), 'updated_at' => now()]);
        }

        return redirect()->back();
    }

    /**
     * Data baris DMS + tally untuk tanggal/shift tertentu — dipakai oleh halaman Formulir (Inertia) dan cetak PDF.
     */
    private function formulirData(string $tanggal, string $shift): array
    {
        $petugasRow = DB::table('icc_dms_petugas')
            ->where('tanggal', $tanggal)
            ->where('shift', $shift)
            ->first();

        $rowsRaw = DB::table('icc_dms_data')
            ->where('tanggal', $tanggal)
            ->where('shift', $shift)
            ->orderBy('id', 'asc')
            ->get();

        $formattedRows = $rowsRaw->map(function ($row) {
            return [
                'id' => $row->id,
                'namaOperator' => $row->nama_operator,
                'nrp' => $row->nrp,
                'noUnit' => $row->unit,
                'area' => $row->area,
                'waktu' => $row->waktu,
                'jenisDeviasi' => $row->jenis_deviasi,
                'deviasiKe' => $row->deviasi_ke,
                'temuan' => $row->temuan,
                'evidenceIcc' => $row->evidence_icc,
                'followUp1' => $row->followup_hasil,
                'followUp2' => $row->followup_hasil_2,
                'note' => $row->note_intervensi,
                'sumber' => $row->sumber,
                'groupLeader' => $row->group_leader,
                'status' => $row->status,
                'remark' => $row->remark ?: '-',
            ];
        })->all();

        $tally = array_fill_keys(self::JENIS_DEVIASI, 0);
        foreach ($formattedRows as $r) {
            if (isset($tally[$r['jenisDeviasi']])) {
                $tally[$r['jenisDeviasi']]++;
            }
        }

        return [
            'petugasRow' => $petugasRow,
            'rows' => $formattedRows,
            'tally' => $tally,
        ];
    }

    /**
     * Cetak Formulir DMS sebagai PDF (Browsershot/Chromium) — tampilan disamakan dengan halaman Formulir.
     */
    public function printFormulirPdf(Request $request)
    {
        [$defaultTanggal, $defaultShift] = $this->currentShiftContext();
        $tanggal = (string) $request->input('tanggal', $defaultTanggal);
        $shift = (string) $request->input('shift', $defaultShift);

        $data = $this->formulirData($tanggal, $shift);
        $settings = $this->formSettings();

        $logoDataUri = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/images.png')));

        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $appCssPath = public_path('build/' . $manifest['resources/css/app.css']['file']);

        $html = view('pdf.formulir-dms', [
            'tanggal' => $tanggal,
            'hariIndo' => \Illuminate\Support\Carbon::parse($tanggal)->locale('id')->translatedFormat('l'),
            'shiftLabel' => $shift === '2' ? 'Shift 2' : 'Shift 1',
            'petugas1' => $data['petugasRow']->petugas1 ?? '-',
            'petugas2' => $data['petugasRow']->petugas2 ?? '-',
            'rows' => $data['rows'],
            'tally' => $data['tally'],
            'logoDataUri' => $logoDataUri,
            'inlineCss' => file_get_contents($appCssPath),
            'docNo' => $settings['docNo'],
            'docRevisi' => $settings['docRevisi'],
            'docTglEfektif' => $settings['docTglEfektif'],
            'docHalaman' => $settings['docHalaman'],
            'tallyTitle' => $settings['tallyTitle'],
        ])->render();

        $browsershot = \Spatie\Browsershot\Browsershot::html($html)
            ->noSandbox()
            ->showBackground()
            ->windowSize(1250, 850)
            ->paperSize(330, 215, 'mm')
            ->margins(6, 6, 6, 6, 'mm');

        if ($chromePath = config('services.browsershot.chrome_path')) {
            $browsershot->setChromePath($chromePath);
        }
        if ($nodeBinary = config('services.browsershot.node_binary')) {
            $browsershot->setNodeBinary($nodeBinary);
        }
        if ($npmBinary = config('services.browsershot.npm_binary')) {
            $browsershot->setNpmBinary($npmBinary);
        }

        $pdf = $browsershot->pdf();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"formulir-dms-{$tanggal}-shift{$shift}.pdf\"",
        ]);
    }

    /**
     * Formulir DMS — Dokumen PPA-ADRO-F-PRO-32B & Input Transaksi DMS
     */
    public function formulir(Request $request): Response
    {
        [$defaultTanggal, $defaultShift] = $this->currentShiftContext();
        $tanggal = (string) $request->input('tanggal', $defaultTanggal);
        $shift = (string) $request->input('shift', $defaultShift);

        ['petugasRow' => $petugasRow, 'rows' => $formattedRows, 'tally' => $tally] = $this->formulirData($tanggal, $shift);

        $petugasList = DB::table('employees')
            ->where('jabatan_ss6', 'like', '%ADMIN%')
            ->whereNotNull('name')
            ->where('name', '<>', '')
            ->distinct()
            ->pluck('name')
            ->sort()
            ->values()
            ->all();

        $rosterKode = $shift == '2' ? 'N' : 'D';

        $rosterLookupRaw = DB::table('roster_data')
            ->leftJoin('employees', 'roster_data.nrp', '=', 'employees.nrp')
            ->leftJoin('employees as gl', 'employees.gl_id', '=', 'gl.id')
            ->where('roster_data.tanggal', $tanggal)
            ->where('roster_data.kode', $rosterKode)
            ->whereNotNull('roster_data.unit')
            ->where('roster_data.unit', '<>', '')
            ->select('roster_data.unit', 'employees.name as namaOperator', 'roster_data.nrp', 'gl.name as fallbackGl')
            ->get();

        // Cari mapping HD ke Loader/Fleet
        $fleetAllocations = DB::table('fleet_allocations')
            ->where('tanggal', $tanggal)
            ->where('shift', $shift)
            ->get()
            ->keyBy(function($item) {
                return strtoupper(trim($item->unit_nrp));
            });

        // Cari PIC (Pengawas) dari Loader/Fleet di tabel legacy icc_gl_settings
        $glSettings = DB::table('icc_gl_settings')
            ->where('tanggal', $tanggal)
            ->where('shift', $shift)
            ->get()
            ->keyBy(function($item) {
                return strtoupper(trim($item->unit));
            });

        $rosterLookup = [];
        foreach ($rosterLookupRaw as $roster) {
            $unitUpper = strtoupper(trim($roster->unit));
            $glName = '';
            $areaName = '';

            // 1. Cek di Fleet Allocation, unit HD ini ikut loader mana
            if (isset($fleetAllocations[$unitUpper])) {
                $loaderId = strtoupper(trim($fleetAllocations[$unitUpper]->loader_id));
                $areaName = $fleetAllocations[$unitUpper]->area;
                
                // 2. Cek siapa pengawas (PIC) loader tersebut di icc_gl_settings.
                // Satu loader bisa punya PIC Front dan PIC Disposal sekaligus — tampilkan keduanya
                // (dipisah " / ") kalau front dan disposal sama-sama terisi, sesuai perilaku V1.
                if (isset($glSettings[$loaderId])) {
                    $pics = array_filter([
                        $glSettings[$loaderId]->front_pic,
                        $glSettings[$loaderId]->disposal_pic,
                    ]);
                    $glName = implode(' / ', $pics);
                }
            }

            // Jika tidak ketemu, gunakan fallback dari atasan langsung
            if (!$glName) {
                $glName = $roster->fallbackGl ?: '';
            }

            $rosterLookup[$unitUpper] = [
                'nama' => $roster->namaOperator ?: ('NRP ' . $roster->nrp),
                'nrp' => $roster->nrp,
                'gl' => $glName,
                'area' => $areaName,
            ];
        }

        return Inertia::render('OneDms/Formulir', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_dms_menu'),
            'filters' => [
                'tanggal' => $tanggal,
                'shift' => $shift,
                'shiftOptions' => ['1' => 'Shift 1', '2' => 'Shift 2'],
            ],
            'petugas' => [
                'petugas1' => $petugasRow->petugas1 ?? '',
                'petugas2' => $petugasRow->petugas2 ?? '',
            ],
            'petugasOptions' => $petugasList,
            'formOptions' => [
                'jenisDeviasi' => self::JENIS_DEVIASI,
                'temuan' => self::TEMUAN_OPTIONS,
                'note' => self::NOTE_OPTIONS,
                'status' => self::STATUS_OPTIONS,
            ],
            'rows' => $formattedRows,
            'tally' => $tally,
            'rosterLookup' => $rosterLookup,
            'docSettings' => $this->formSettings(),
        ]);
    }

    public function storeDms(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'tanggal' => 'required|date',
            'shift' => 'required|string',
            'unit' => 'required|string',
            'jenis_deviasi' => 'required|string',
            'nama_operator' => 'nullable|string',
            'nrp' => 'nullable|string',
            'area' => 'nullable|string',
            'waktu' => 'nullable|string',
            'deviasi_ke' => 'nullable|integer',
            'temuan' => 'nullable|string',
            'evidence_icc' => 'nullable|string',
            'followup_hasil' => 'nullable|string',
            'followup_hasil_2' => 'nullable|string',
            'note_intervensi' => 'nullable|string',
            'sumber' => 'nullable|string',
            'group_leader' => 'nullable|string',
            'status' => 'required|string',
            'remark' => 'nullable|string',
        ]);

        $petugasRow = DB::table('icc_dms_petugas')
            ->where('tanggal', $validated['tanggal'])
            ->where('shift', $validated['shift'])
            ->first();

        $data = [
            'tanggal' => $validated['tanggal'],
            'shift' => $validated['shift'],
            'petugas1' => $petugasRow->petugas1 ?? '',
            'petugas2' => $petugasRow->petugas2 ?? '',
            'unit' => strtoupper(trim($validated['unit'])),
            'nama_operator' => trim($validated['nama_operator'] ?? ''),
            'nrp' => trim($validated['nrp'] ?? ''),
            'area' => trim($validated['area'] ?? ''),
            'waktu' => trim($validated['waktu'] ?? ''),
            'jenis_deviasi' => trim($validated['jenis_deviasi']),
            'deviasi_ke' => $validated['deviasi_ke'] ?? 1,
            'temuan' => trim($validated['temuan'] ?? ''),
            'evidence_icc' => trim($validated['evidence_icc'] ?? ''),
            'followup_hasil' => trim($validated['followup_hasil'] ?? ''),
            'followup_hasil_2' => trim($validated['followup_hasil_2'] ?? ''),
            'note_intervensi' => trim($validated['note_intervensi'] ?? ''),
            'sumber' => trim($validated['sumber'] ?? ''),
            'group_leader' => trim($validated['group_leader'] ?? ''),
            'status' => trim($validated['status']),
            'remark' => trim($validated['remark'] ?? ''),
            'updated_at' => now(),
        ];

        if (!empty($validated['id'])) {
            $existing = (array) DB::table('icc_dms_data')->where('id', $validated['id'])->first();
            DB::table('icc_dms_data')->where('id', $validated['id'])->update($data);
            $id = $validated['id'];
        } else {
            $data['created_at'] = now();
            $id = DB::table('icc_dms_data')->insertGetId($data);
            $existing = [];
        }

        $this->syncToDataIcc($id, $data, $existing);

        return redirect()->back()->with('success', 'Data DMS berhasil disimpan.');
    }

    public function deleteDms($id)
    {
        $dms = DB::table('icc_dms_data')->where('id', $id)->first();
        if ($dms) {
            if ($dms->synced_adas_id) {
                DB::table('fatigue_adas_alarm')->where('id', $dms->synced_adas_id)->delete();
            }
            if ($dms->synced_fatigue_id) {
                DB::table('fatigue_data')->where('id', $dms->synced_fatigue_id)->delete();
            }
            if ($dms->synced_smoking_id) {
                DB::table('smoking_deviasi')->where('id', $dms->synced_smoking_id)->delete();
            }
            DB::table('icc_dms_data')->where('id', $id)->delete();
        }

        return redirect()->back()->with('success', 'Data DMS berhasil dihapus.');
    }

    public function savePetugas(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'shift' => 'required|string',
            'petugas1' => 'nullable|string',
            'petugas2' => 'nullable|string',
        ]);

        DB::table('icc_dms_petugas')->updateOrInsert(
            ['tanggal' => $validated['tanggal'], 'shift' => $validated['shift']],
            ['petugas1' => $validated['petugas1'] ?? '', 'petugas2' => $validated['petugas2'] ?? '', 'updated_at' => now()]
        );

        return redirect()->back()->with('success', 'Petugas DMS berhasil diperbarui.');
    }

    public function dataIcc(Request $request, string $category): Response
    {
        $tanggal = (string) $request->input('tanggal', now()->format('Y-m'));
        $search = trim((string) $request->input('q', ''));

        if ($category === 'deviasi-fatigue') {
            $query = DB::table('fatigue_data')->where('tanggal', 'like', $tanggal . '%');
            $title = 'Data ICC - Deviasi Fatigue';
        } elseif ($category === 'smoking-deviasi') {
            $query = DB::table('smoking_deviasi')->where('tanggal', 'like', $tanggal . '%');
            $title = 'Data ICC - Smoking Deviasi';
        } elseif ($category === 'izin-fatigue') {
            if (!\Illuminate\Support\Facades\Schema::hasTable('izin_fatigue_data')) {
                \Illuminate\Support\Facades\Artisan::call('migrate');
            }
            $query = DB::table('izin_fatigue_data')->where('tanggal', 'like', $tanggal . '%');
            $title = 'Data ICC - Izin Fatigue';
        } else {
            $adasName = self::ADAS_MAP[$category] ?? 'Driver Distraction';
            $query = DB::table('fatigue_adas_alarm')
                ->where('tanggal', 'like', $tanggal . '%')
                ->where('jenis_deviasi', $adasName);
            $title = "Data ICC - {$adasName}";
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search, $category) {
                $q->where('nama_operator', 'like', "%{$search}%")
                  ->orWhere('nrp', 'like', "%{$search}%");
                if ($category !== 'izin-fatigue') {
                    $q->orWhere('unit', 'like', "%{$search}%");
                }
            });
        }

        $records = $query->orderBy('id', 'desc')->paginate(25);

        $nrpLookup = [];
        if ($category === 'izin-fatigue') {
            $emps = DB::table('employees')->whereNotNull('nrp')->get(['nrp', 'name']);
            foreach ($emps as $e) {
                $nrpLookup[$e->nrp] = $e->name;
            }
        }

        return Inertia::render('OneDms/DataIccIndex', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_dms_menu'),
            'category' => $category,
            'title' => $title,
            'filters' => [
                'tanggal' => $tanggal,
                'search' => $search,
            ],
            'records' => $records,
            'nrpLookup' => $nrpLookup,
        ]);
    }

    public function saveIzinFatigue(StoreIzinFatigueRequest $request)
    {
        $validated = $request->validated();

        $data = [
            'tanggal' => $validated['tanggal'],
            'shift' => $validated['shift'],
            'nrp' => $validated['nrp'] ?? '',
            'nama_operator' => $validated['nama_operator'] ?? '',
            'jam_mulai' => $validated['jam_mulai'] ?? '',
            'jam_akhir' => $validated['jam_akhir'] ?? '',
            'total_jam' => $validated['total_jam'] ?? '',
            'updated_at' => now(),
        ];

        if (!empty($validated['id'])) {
            DB::table('izin_fatigue_data')->where('id', $validated['id'])->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('izin_fatigue_data')->insert($data);
        }

        return redirect()->back()->with('success', 'Data Izin Fatigue berhasil disimpan.');
    }

    public function deleteIcc(Request $request, $id)
    {
        $category = $request->input('category');
        
        if ($category === 'izin-fatigue') {
            DB::table('izin_fatigue_data')->where('id', $id)->delete();
        } elseif ($category === 'smoking-deviasi') {
            DB::table('smoking_deviasi')->where('id', $id)->delete();
        } elseif ($category === 'deviasi-fatigue') {
            DB::table('fatigue_data')->where('id', $id)->delete();
        } else {
            DB::table('fatigue_adas_alarm')->where('id', $id)->delete();
        }
        
        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }

    public function saveIcc(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'category' => 'required|string',
            'tanggal' => 'required|date',
            'shift' => 'required|string',
            'nrp' => 'nullable|string',
            'nama_operator' => 'nullable|string',
            'unit' => 'nullable|string',
            'area' => 'nullable|string',
            'group_leader' => 'nullable|string',
            'status' => 'required|string',
            'waktu' => 'nullable|string',
            'deviasi_ke' => 'nullable|integer',
            'intervensi' => 'nullable|string',
            'evidence' => 'nullable|string',
            'fu' => 'nullable|string',
        ]);

        $category = $validated['category'];
        $id = $validated['id'] ?? null;

        $baseData = [
            'tanggal' => $validated['tanggal'],
            'shift' => $validated['shift'],
            'nrp' => $validated['nrp'] ?? '',
            'nama_operator' => $validated['nama_operator'] ?? '',
            'unit' => $validated['unit'] ?? '',
            'area' => $validated['area'] ?? '',
            'group_leader' => $validated['group_leader'] ?? '',
            'status' => $validated['status'],
            'updated_at' => now(),
        ];

        if ($category === 'smoking-deviasi') {
            $table = 'smoking_deviasi';
            $data = array_merge($baseData, [
                'evidence' => $validated['evidence'] ?? '',
                'fu' => $validated['fu'] ?? '',
            ]);
        } else {
            $table = $category === 'deviasi-fatigue' ? 'fatigue_data' : 'fatigue_adas_alarm';
            $data = array_merge($baseData, [
                'waktu' => $validated['waktu'] ?? '',
                'deviasi_ke' => $validated['deviasi_ke'] ?? 1,
                'intervensi' => $validated['intervensi'] ?? '',
                'jenis_deviasi' => $category === 'deviasi-fatigue' ? 'Driver Fatigue' : (self::ADAS_MAP[$category] ?? 'Driver Distraction'),
            ]);
        }

        if ($id) {
            DB::table($table)->where('id', $id)->update($data);
        } else {
            $data['created_at'] = now();
            DB::table($table)->insert($data);
        }

        return redirect()->back()->with('success', $id ? 'Data berhasil diperbarui.' : 'Data berhasil ditambahkan.');
    }

    public function importIcc(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'category' => 'required|string'
        ]);

        $file = $request->file('file');
        $category = $request->input('category');
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            $count = 0;
            $offset = 0;
            if (!empty($rows[0]) && strtolower(trim((string)$rows[0][0])) === 'no') {
                $offset = 1;
            }

            $headers = array_map(function($h) { return strtolower(trim((string)$h)); }, $rows[0] ?? []);
            $colMap = [];
            foreach ($headers as $idx => $header) {
                if (str_contains($header, 'tanggal')) $colMap['tanggal'] = $idx;
                elseif (str_contains($header, 'shift')) $colMap['shift'] = $idx;
                elseif (str_contains($header, 'nama')) $colMap['nama_operator'] = $idx;
                elseif (str_contains($header, 'nrp')) $colMap['nrp'] = $idx;
                elseif (str_contains($header, 'unit')) $colMap['unit'] = $idx;
                elseif (str_contains($header, 'area')) $colMap['area'] = $idx;
                elseif (str_contains($header, 'waktu')) $colMap['waktu'] = $idx;
                elseif (str_contains($header, 'jenis deviasi')) $colMap['jenis_deviasi'] = $idx;
                elseif (str_contains($header, 'deviasi ke')) $colMap['deviasi_ke'] = $idx;
                elseif (str_contains($header, 'intervensi')) $colMap['intervensi'] = $idx;
                elseif (str_contains($header, 'evidence')) $colMap['evidence'] = $idx;
                elseif (str_contains($header, 'follow') || str_contains($header, 'fu')) $colMap['fu'] = $idx;
                elseif (str_contains($header, 'group') || str_contains($header, 'gl')) $colMap['group_leader'] = $idx;
                elseif (str_contains($header, 'status')) $colMap['status'] = $idx;
                elseif (str_contains($header, 'mulai')) $colMap['jam_mulai'] = $idx;
                elseif (str_contains($header, 'akhir')) $colMap['jam_akhir'] = $idx;
                elseif (str_contains($header, 'total')) $colMap['total_jam'] = $idx;
            }

            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // skip header
                
                $val = function($key, $fallback) use ($row, $colMap) {
                    if (isset($colMap[$key]) && isset($row[$colMap[$key]])) return $row[$colMap[$key]];
                    return $row[$fallback] ?? '';
                };
                
                $tanggal = $val('tanggal', $offset);
                if (empty($tanggal)) continue;
                
                if (is_numeric($tanggal)) {
                    $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggal)->format('Y-m-d');
                } else {
                    $tanggal = date('Y-m-d', strtotime($tanggal));
                }
                
                if ($category === 'izin-fatigue') {
                    DB::table('izin_fatigue_data')->insert([
                        'tanggal' => $tanggal,
                        'shift' => $val('shift', $offset + 1) ?: 'Shift 1',
                        'nrp' => $val('nrp', $offset + 2),
                        'nama_operator' => $val('nama_operator', $offset + 3),
                        'jam_mulai' => $val('jam_mulai', $offset + 4),
                        'jam_akhir' => $val('jam_akhir', $offset + 5),
                        'total_jam' => $val('total_jam', $offset + 6),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } elseif ($category === 'smoking-deviasi') {
                    DB::table('smoking_deviasi')->insert([
                        'tanggal' => $tanggal,
                        'shift' => $val('shift', $offset + 1) ?: 'Shift 1',
                        'nama_operator' => $val('nama_operator', $offset + 2),
                        'nrp' => $val('nrp', $offset + 3),
                        'unit' => $val('unit', $offset + 4),
                        'area' => $val('area', $offset + 5),
                        'evidence' => $val('evidence', $offset + 6),
                        'fu' => $val('fu', $offset + 7),
                        'group_leader' => $val('group_leader', $offset + 8),
                        'status' => $val('status', $offset + 9) ?: 'Close',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $jenisDeviasi = self::ADAS_MAP[$category] ?? 'Driver Distraction';
                    if ($category === 'deviasi-fatigue') {
                        $table = 'fatigue_data';
                        $jenisDeviasi = 'Driver Fatigue';
                    } else {
                        $table = 'fatigue_adas_alarm';
                    }
                    
                    $adasOffset = isset($colMap['jenis_deviasi']) || (isset($rows[0]) && str_contains(strtolower((string)($rows[0][$offset+7] ?? '')), 'jenis')) ? 1 : 0;
                    
                    DB::table($table)->insert([
                        'tanggal' => $tanggal,
                        'shift' => $val('shift', $offset + 1) ?: 'Shift 1',
                        'nama_operator' => $val('nama_operator', $offset + 2),
                        'nrp' => $val('nrp', $offset + 3),
                        'unit' => $val('unit', $offset + 4),
                        'area' => $val('area', $offset + 5),
                        'waktu' => $val('waktu', $offset + 6),
                        'deviasi_ke' => (int)$val('deviasi_ke', $offset + 7 + $adasOffset) ?: 1,
                        'intervensi' => $val('intervensi', $offset + 8 + $adasOffset),
                        'group_leader' => $val('group_leader', $offset + 9 + $adasOffset),
                        'status' => $val('status', $offset + 10 + $adasOffset) ?: 'Close',
                        'jenis_deviasi' => $jenisDeviasi,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                $count++;
            }

            return redirect()->back()->with('success', "Berhasil import $count data.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import Excel: ' . $e->getMessage());
        }
    }

    private function syncToDataIcc(int $dmsId, array $dms, array $existing = []): void
    {
        $closed = $dms['status'] === 'Closed';
        $jenis = $dms['jenis_deviasi'];
        $shiftLabel = $dms['shift'] === '2' ? 'Shift 2' : 'Shift 1';

        // Retain existing sync IDs if they exist
        $syncedAdasId = $existing['synced_adas_id'] ?? null;
        $syncedFatigueId = $existing['synced_fatigue_id'] ?? null;
        $syncedSmokingId = $existing['synced_smoking_id'] ?? null;

        if ($closed) {
            if ($jenis === 'Driver Fatigue') {
                $payload = [
                    'tanggal' => $dms['tanggal'],
                    'shift' => $shiftLabel,
                    'nama_operator' => $dms['nama_operator'],
                    'nrp' => $dms['nrp'],
                    'unit' => $dms['unit'],
                    'area' => $dms['area'],
                    'waktu' => $dms['waktu'],
                    'jenis_deviasi' => 'Driver Fatigue',
                    'deviasi_ke' => $dms['deviasi_ke'] ?: 1,
                    'intervensi' => $dms['note_intervensi'],
                    'group_leader' => $dms['group_leader'],
                    'status' => 'Close',
                    'updated_at' => now(),
                ];
                if ($syncedFatigueId) {
                    DB::table('fatigue_data')->where('id', $syncedFatigueId)->update($payload);
                } else {
                    $payload['created_at'] = now();
                    $syncedFatigueId = DB::table('fatigue_data')->insertGetId($payload);
                }
            } elseif ($jenis === 'Smoking Detection') {
                $payload = [
                    'tanggal' => $dms['tanggal'],
                    'shift' => $shiftLabel,
                    'unit' => $dms['unit'],
                    'area' => $dms['area'],
                    'nama_operator' => $dms['nama_operator'],
                    'nrp' => $dms['nrp'],
                    'evidence' => $dms['evidence_icc'],
                    'fu' => $dms['followup_hasil'],
                    'group_leader' => $dms['group_leader'],
                    'status' => 'Close',
                    'updated_at' => now(),
                ];
                if ($syncedSmokingId) {
                    DB::table('smoking_deviasi')->where('id', $syncedSmokingId)->update($payload);
                } else {
                    $payload['created_at'] = now();
                    $syncedSmokingId = DB::table('smoking_deviasi')->insertGetId($payload);
                }
            } else {
                $payload = [
                    'tanggal' => $dms['tanggal'],
                    'shift' => $shiftLabel,
                    'nama_operator' => $dms['nama_operator'],
                    'nrp' => $dms['nrp'],
                    'unit' => $dms['unit'],
                    'area' => $dms['area'],
                    'waktu' => $dms['waktu'],
                    'jenis_deviasi' => $jenis,
                    'deviasi_ke' => $dms['deviasi_ke'] ?: 1,
                    'intervensi' => $dms['note_intervensi'],
                    'group_leader' => $dms['group_leader'],
                    'status' => 'Close',
                    'updated_at' => now(),
                ];
                if ($syncedAdasId) {
                    DB::table('fatigue_adas_alarm')->where('id', $syncedAdasId)->update($payload);
                } else {
                    $payload['created_at'] = now();
                    $syncedAdasId = DB::table('fatigue_adas_alarm')->insertGetId($payload);
                }
            }
        } else {
            // If it was closed but now it's opened again, we could delete it from ICC data or just leave it.
            // Usually we leave it or delete it. Let's delete it so it's consistent.
            if ($syncedAdasId) {
                DB::table('fatigue_adas_alarm')->where('id', $syncedAdasId)->delete();
                $syncedAdasId = null;
            }
            if ($syncedFatigueId) {
                DB::table('fatigue_data')->where('id', $syncedFatigueId)->delete();
                $syncedFatigueId = null;
            }
            if ($syncedSmokingId) {
                DB::table('smoking_deviasi')->where('id', $syncedSmokingId)->delete();
                $syncedSmokingId = null;
            }
        }

        DB::table('icc_dms_data')->where('id', $dmsId)->update([
            'synced_adas_id' => $syncedAdasId,
            'synced_fatigue_id' => $syncedFatigueId,
            'synced_smoking_id' => $syncedSmokingId,
        ]);
    }
}
