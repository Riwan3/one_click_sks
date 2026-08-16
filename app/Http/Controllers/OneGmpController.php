<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OneGmpController extends Controller
{
    private const GMP_TYPES = [
        'MFOB' => 'Front OB',
        'MFCO' => 'Front Coal Getting',
        'DISPOSAL' => 'Disposal',
        'MHR' => 'Jalan (MHR)',
        'DEWATERING' => 'Dewatering',
    ];

    private const ASPECTS = [
        'Kebersihan Area', 'Rambu K3', 'APD Lengkap', 'Kondisi Unit',
        'Prosedur Kerja', 'House Keeping', 'Emergency Response', 'Dokumentasi',
    ];

    private const LOCATIONS = [
        'Pit A1', 'Pit A2', 'Pit A3', 'Pit A4', 'Pit A5',
        'Pit B1', 'Pit B2', 'Pit B3', 'Pit B4', 'Pit B5',
        'Disposal 1', 'Disposal 2', 'Disposal 3', 'Workshop', 'Jalan Hauling',
    ];

    /**
     * Dummy data — ganti dengan query database (gmp_penilaian_form dkk) begitu backend One-GMP siap.
     */
    public function dashboard(Request $request): Response
    {
        $gmpType = strtoupper((string) $request->input('gmp_type', 'MFOB'));
        if (! array_key_exists($gmpType, self::GMP_TYPES)) {
            $gmpType = 'MFOB';
        }
        $year = (int) $request->integer('tahun', now()->year);
        $availableWeeks = range(1, 8);
        $week = (int) $request->integer('week', end($availableWeeks));

        $seed = crc32($gmpType) + $week * 7 + $year;

        $paretoData = $this->paretoDataFor($seed);
        $lokasiData = $this->lokasiDataFor($seed);

        return Inertia::render('OneGmp/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => [
                'signature' => 2,
                'notification' => 3,
                'message' => 1,
            ],
            'menu' => config('one_gmp_menu'),
            'filters' => [
                'gmpType' => $gmpType,
                'gmpTypeOptions' => self::GMP_TYPES,
                'week' => $week,
                'weekOptions' => $availableWeeks,
                'tahun' => $year,
                'years' => range(now()->year - 2, now()->year),
                'weekLabel' => "Week {$week} - {$year}",
            ],
            'stats' => $this->statsFrom($paretoData, $seed),
            'pareto' => $paretoData,
            'lokasi' => $lokasiData,
            'lokasiDetail' => $this->lokasiDetailFor($seed),
        ]);
    }

    private function paretoDataFor(int $seed): array
    {
        $values = collect(self::ASPECTS)->values()->map(fn (string $aspect, int $index) => [
            'label' => $aspect,
            'value' => round(60 + (sin($seed + $index) + 1) * 18, 1),
        ])->sortByDesc('value')->values();

        $total = $values->sum('value');
        $cumulative = 0;

        return $values->map(function (array $item) use ($total, &$cumulative) {
            $cumulative += $item['value'];

            return [...$item, 'cumulative' => round($total > 0 ? $cumulative / $total * 100 : 0, 1)];
        })->all();
    }

    private function lokasiDataFor(int $seed): array
    {
        return collect(self::LOCATIONS)->map(fn (string $lokasi, int $index) => [
            'label' => $lokasi,
            'value' => round(55 + (cos($seed + $index * 3) + 1) * 20, 1),
        ])->sortByDesc('value')->values()->all();
    }

    private function lokasiDetailFor(int $seed): array
    {
        return collect(self::LOCATIONS)->map(fn (string $lokasi, int $locationIndex) => [
            'label' => $lokasi,
            'aspects' => collect(self::ASPECTS)->map(fn (string $aspect, int $index) => [
                'label' => $aspect,
                'value' => round(50 + (sin($seed + $locationIndex + $index) + 1) * 22, 1),
            ])->all(),
        ])->all();
    }

    private function statsFrom(array $paretoData, int $seed): array
    {
        $avg = round(collect($paretoData)->avg('value'), 1);

        return [
            'totalPenilaian' => 120 + $seed % 60,
            'avgScore' => $avg,
            'maxScore' => round(collect($paretoData)->max('value'), 1),
            'minScore' => round(collect($paretoData)->min('value'), 1),
        ];
    }
}
