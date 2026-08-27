<?php

namespace App\Http\Controllers;

use App\Support\MenuAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OneReportingController extends Controller
{
    private const ITEM_COMPLIANCE = [
        'Kelengkapan APD', 'Speed Limit', 'Seatbelt', 'Jarak Aman',
        'Prosedur P5M', 'Body Checking', 'Fatigue Test', 'Kelengkapan Dokumen',
    ];

    private const STATUS_OPTIONS = ['Compliant', 'Non-Compliant'];

    private const OPERATORS = ['Ahmad Fauzi', 'Budi Santoso', 'Candra Wijaya', 'Dedi Kurniawan', 'Eko Prasetyo', 'Fajar Nugroho', 'Gilang Ramadhan', 'Hendra Saputra'];

    private const GROUP_LEADERS = ['Rian Hidayat', 'Dewi Anggraini', 'Bayu Saputra', 'Nina Marlina'];

    private const UNITS = ['HD785-012', 'PC2000-004', 'D375-007', 'WA500-009', 'GD675-002', 'HD785-018', 'PC2000-011', 'D375-014'];

    /**
     * Dummy data — ganti dengan query database (compliance report dkk) begitu backend One-Reporting siap.
     */
    public function dashboard(Request $request): Response
    {
        $tanggal = (string) $request->input('tanggal', now()->format('Y-m-d'));
        $shift = (string) $request->input('shift', '1');

        $rows = $this->dummyRows($tanggal);

        return Inertia::render('OneReporting/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => [
                'signature' => 2,
                'notification' => 3,
                'message' => 1,
            ],
            'menu' => MenuAccess::forConfig('one_reporting_menu'),
            'filters' => [
                'tanggal' => $tanggal,
                'shift' => $shift,
                'shiftOptions' => ['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3'],
            ],
            'stats' => $this->statsFrom($rows),
            'rows' => $rows,
        ]);
    }

    private function statsFrom(array $rows): array
    {
        $total = count($rows);
        $compliant = collect($rows)->where('status', 'Compliant')->count();
        $nonCompliant = $total - $compliant;

        return [
            'totalAssignment' => $total,
            'totalCompliant' => $compliant,
            'totalNonCompliant' => $nonCompliant,
            'complianceRate' => $total > 0 ? round($compliant / $total * 100, 1) : 0,
        ];
    }

    private function dummyRows(string $tanggal): array
    {
        $seed = crc32($tanggal);

        return collect(range(0, 9))->map(fn (int $index) => [
            'id' => $index + 1,
            'tanggal' => $tanggal,
            'noUnit' => self::UNITS[$index % count(self::UNITS)],
            'namaOperator' => self::OPERATORS[$index % count(self::OPERATORS)],
            'groupLeader' => self::GROUP_LEADERS[($index + $seed) % count(self::GROUP_LEADERS)],
            'itemCompliance' => self::ITEM_COMPLIANCE[$index % count(self::ITEM_COMPLIANCE)],
            'status' => self::STATUS_OPTIONS[($index + $seed) % 3 === 0 ? 1 : 0],
            'keterangan' => ($index + $seed) % 3 === 0 ? 'Perlu tindak lanjut group leader' : '-',
        ])->all();
    }
}
