<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OneDmsController extends Controller
{
    private const JENIS_DEVIASI = [
        'Driver Fatigue', 'Driver Distraction', 'Phone Detection', 'Smoking Detection',
        'Yawning Detection', 'Eye Blink', 'Camera Covering', 'Forward Collision',
        'Video Loss Alarm', 'No Driver', 'Abnormal Speed Indication',
    ];

    private const TEMUAN_OPTIONS = ['NOTIF', 'LIVE'];

    private const NOTE_OPTIONS = ['Sudah ditegur lisan', 'Sudah diberikan SP', 'Dalam pemantauan', 'Selesai ditindaklanjuti'];

    private const STATUS_OPTIONS = ['Open', 'In Progress', 'Closed'];

    private const PETUGAS = ['Rian Hidayat', 'Dewi Anggraini', 'Bayu Saputra', 'Nina Marlina', 'Agus Salim', 'Lestari Handayani'];

    /**
     * Dummy data — ganti dengan query database begitu backend One-DMS siap.
     */
    public function dashboard(Request $request): Response
    {
        $tanggal = (string) $request->input('tanggal', now()->format('Y-m-d'));
        $shift = (string) $request->input('shift', '1');

        $rows = $this->dummyRows();

        return Inertia::render('OneDms/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => [
                'signature' => 2,
                'notification' => 3,
                'message' => 1,
            ],
            'menu' => config('one_dms_menu'),
            'filters' => [
                'tanggal' => $tanggal,
                'shift' => $shift,
                'shiftOptions' => ['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3'],
            ],
            'petugasOptions' => self::PETUGAS,
            'formOptions' => [
                'jenisDeviasi' => self::JENIS_DEVIASI,
                'temuan' => self::TEMUAN_OPTIONS,
                'note' => self::NOTE_OPTIONS,
                'status' => self::STATUS_OPTIONS,
            ],
            'rows' => $rows,
            'tally' => $this->tallyFrom($rows),
        ]);
    }

    private function tallyFrom(array $rows): array
    {
        $counts = array_fill_keys(self::JENIS_DEVIASI, 0);

        foreach ($rows as $row) {
            if (isset($counts[$row['jenisDeviasi']])) {
                $counts[$row['jenisDeviasi']]++;
            }
        }

        return $counts;
    }

    private function dummyRows(): array
    {
        $names = ['Ahmad Fauzi', 'Budi Santoso', 'Candra Wijaya', 'Dedi Kurniawan', 'Eko Prasetyo', 'Fajar Nugroho', 'Gilang Ramadhan', 'Hendra Saputra'];
        $units = ['HD785-012', 'PC2000-004', 'D375-007', 'WA500-009', 'GD675-002', 'HD785-018', 'PC2000-011', 'D375-014'];
        $areas = ['Pit A', 'Pit B', 'Disposal', 'Workshop Area', 'Front Loading'];
        $statuses = self::STATUS_OPTIONS;

        return collect(range(0, 7))->map(fn (int $index) => [
            'id' => $index + 1,
            'namaOperator' => $names[$index],
            'nrp' => (string) (20001 + $index),
            'noUnit' => $units[$index],
            'area' => $areas[$index % count($areas)],
            'waktu' => sprintf('%02d:%02d', 6 + $index, ($index * 7) % 60),
            'jenisDeviasi' => self::JENIS_DEVIASI[$index % count(self::JENIS_DEVIASI)],
            'deviasiKe' => 1 + ($index % 3),
            'temuan' => self::TEMUAN_OPTIONS[$index % 2],
            'evidenceIcc' => null,
            'followUp1' => null,
            'followUp2' => null,
            'note' => self::NOTE_OPTIONS[$index % count(self::NOTE_OPTIONS)],
            'sumber' => $index % 2 === 0 ? 'ICC' : 'DMS',
            'groupLeader' => $names[($index + 3) % count($names)],
            'status' => $statuses[$index % count($statuses)],
            'remark' => '-',
        ])->all();
    }
}
