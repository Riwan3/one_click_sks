<?php

namespace App\Http\Controllers;

use App\Support\MenuAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OneDmsController extends Controller
{
    private const JENIS_DEVIASI = [
        'Driver Fatigue', 'Driver Distraction', 'Phone Detection', 'Smoking Detection',
        'Yawning Detection', 'Eye Blink', 'Camera Covering', 'Forward Collision',
        'Video loss alarm', 'No Driver', 'Abnormal Speed Indication',
    ];

    private const ADAS_MAP = [
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
    private const NOTE_OPTIONS = ['Sudah ditegur lisan', 'Sudah diberikan SP', 'Dalam pemantauan', 'Selesai ditindaklanjuti'];
    private const STATUS_OPTIONS = ['Open', 'In Progress', 'Closed'];

    /**
     * Dashboard DMS — Grafik Statistik & Metric Analytics
     */
    public function dashboard(Request $request): Response
    {
        $bulan = (string) $request->input('bulan', now()->format('m'));
        $tahun = (string) $request->input('tahun', now()->format('Y'));
        $shift = (string) $request->input('shift', '');

        $query = DB::table('icc_dms_data')
            ->whereYear('tanggal', $tahun);

        if ($bulan !== '') {
            $query->whereMonth('tanggal', $bulan);
        }
        if ($shift !== '') {
            $query->where('shift', $shift);
        }

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

        // Top 10 Operators
        $topOperators = (clone $query)
            ->select('nama_operator', 'nrp', DB::raw('COUNT(*) as total_cases'))
            ->whereNotNull('nama_operator')
            ->where('nama_operator', '<>', '')
            ->groupBy('nama_operator', 'nrp')
            ->orderByDesc('total_cases')
            ->limit(10)
            ->get();

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
            'topOperators' => $topOperators,
        ]);
    }

    /**
     * Formulir DMS — Dokumen PPA-ADRO-F-PRO-32B & Input Transaksi DMS
     */
    public function formulir(Request $request): Response
    {
        $tanggal = (string) $request->input('tanggal', now()->format('Y-m-d'));
        $shift = (string) $request->input('shift', '1');

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

        $petugasList = DB::table('employees')
            ->where('jabatan_ss6', 'like', '%ADMIN%')
            ->whereNotNull('name')
            ->where('name', '<>', '')
            ->distinct()
            ->pluck('name')
            ->sort()
            ->values()
            ->all();

        $tally = array_fill_keys(self::JENIS_DEVIASI, 0);
        foreach ($formattedRows as $r) {
            if (isset($tally[$r['jenisDeviasi']])) {
                $tally[$r['jenisDeviasi']]++;
            }
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
            DB::table('icc_dms_data')->where('id', $validated['id'])->update($data);
            $id = $validated['id'];
        } else {
            $data['created_at'] = now();
            $id = DB::table('icc_dms_data')->insertGetId($data);
        }

        $this->syncToDataIcc($id, $data);

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
        $tanggal = (string) $request->input('tanggal', now()->format('Y-m-d'));
        $search = trim((string) $request->input('q', ''));

        if ($category === 'deviasi-fatigue') {
            $query = DB::table('fatigue_data')->where('tanggal', $tanggal);
            $title = 'Data ICC - Deviasi Fatigue';
        } elseif ($category === 'smoking-deviasi') {
            $query = DB::table('smoking_deviasi')->where('tanggal', $tanggal);
            $title = 'Data ICC - Smoking Deviasi';
        } else {
            $adasName = self::ADAS_MAP[$category] ?? 'Driver Distraction';
            $query = DB::table('fatigue_adas_alarm')
                ->where('tanggal', $tanggal)
                ->where('jenis_deviasi', $adasName);
            $title = "Data ICC - {$adasName}";
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_operator', 'like', "%{$search}%")
                  ->orWhere('nrp', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy('id', 'desc')->paginate(25);

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
        ]);
    }

    private function syncToDataIcc(int $dmsId, array $dms): void
    {
        $closed = $dms['status'] === 'Closed';
        $jenis = $dms['jenis_deviasi'];
        $shiftLabel = $dms['shift'] === '2' ? 'Shift 2' : 'Shift 1';

        $syncedAdasId = null;
        $syncedFatigueId = null;
        $syncedSmokingId = null;

        if ($closed) {
            if ($jenis === 'Driver Fatigue') {
                $syncedFatigueId = DB::table('fatigue_data')->insertGetId([
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
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif ($jenis === 'Smoking Detection') {
                $syncedSmokingId = DB::table('smoking_deviasi')->insertGetId([
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
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $syncedAdasId = DB::table('fatigue_adas_alarm')->insertGetId([
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
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('icc_dms_data')->where('id', $dmsId)->update([
            'synced_adas_id' => $syncedAdasId,
            'synced_fatigue_id' => $syncedFatigueId,
            'synced_smoking_id' => $syncedSmokingId,
        ]);
    }
}
