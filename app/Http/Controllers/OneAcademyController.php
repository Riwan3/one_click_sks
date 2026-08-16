<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OneAcademyController extends Controller
{
    private const TAHAPAN_OPTIONS = [
        'Pengajuan Baru', 'Menunggu Approval', 'Menunggu Jadwal',
        'Sedang Berlangsung', 'Selesai', 'Lulus', 'Dibatalkan',
    ];

    private const JENIS_OPTIONS = ['Fresh Training', 'Skill Up Training', 'Cross Training', 'Similar Training'];

    /**
     * Dummy data — ganti dengan query database begitu backend One-Academy siap.
     */
    public function dashboard(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $tahapan = (string) $request->string('tahapan');

        $submissions = $this->dummySubmissions();

        $filtered = collect($submissions)
            ->when($search !== '', fn ($rows) => $rows->filter(
                fn (array $row) => Str::contains(strtolower($row['typeUnit']), strtolower($search))
                    || Str::contains(strtolower($row['diajukanNama']), strtolower($search))
                    || Str::contains(strtolower($row['jenisPengajuan']), strtolower($search))
            ))
            ->when($tahapan !== '' && $tahapan !== 'Semua Tahapan', fn ($rows) => $rows->filter(
                fn (array $row) => $row['tahapan'] === $tahapan
            ))
            ->values();

        return Inertia::render('OneAcademy/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => [
                'signature' => 2,
                'notification' => 3,
                'message' => 1,
            ],
            'menu' => config('one_academy_menu'),
            'stats' => $this->statsFrom($submissions),
            'filters' => [
                'search' => $search,
                'tahapan' => $tahapan,
                'tahapanOptions' => array_merge(['Semua Tahapan'], self::TAHAPAN_OPTIONS),
            ],
            'submissions' => $filtered->all(),
        ]);
    }

    private function statsFrom(array $submissions): array
    {
        $collection = collect($submissions);

        return [
            'total' => $collection->count(),
            'baru' => $collection->where('tahapan', 'Pengajuan Baru')->count(),
            'berlangsung' => $collection->whereIn('tahapan', ['Sedang Berlangsung', 'Menunggu Jadwal'])->count(),
            'selesai' => $collection->whereIn('tahapan', ['Selesai', 'Lulus'])->count(),
        ];
    }

    private function dummySubmissions(): array
    {
        $names = ['Ahmad Fauzi', 'Budi Santoso', 'Candra Wijaya', 'Dedi Kurniawan', 'Eko Prasetyo', 'Fajar Nugroho', 'Gilang Ramadhan', 'Hendra Saputra', 'Irfan Hakim', 'Joko Susilo', 'Wahyu Ananda', 'Yusuf Maulana'];
        $units = ['Excavator PC2000', 'Dump Truck HD785', 'Bulldozer D375', 'Grader GD675', 'Wheel Loader WA500', 'Drilling Machine', 'Dozer D85ESS', 'Compactor SD100'];

        return collect(range(0, 11))->map(function (int $index) use ($names, $units) {
            $tahapan = self::TAHAPAN_OPTIONS[$index % count(self::TAHAPAN_OPTIONS)];

            return [
                'id' => $index + 1,
                'tanggal' => now()->subDays($index * 3)->format('d M Y'),
                'jam' => now()->subDays($index * 3)->format('H:i'),
                'jenisPengajuan' => self::JENIS_OPTIONS[$index % count(self::JENIS_OPTIONS)],
                'typeUnit' => $units[$index % count($units)],
                'diajukanNama' => $names[$index % count($names)],
                'totalPeserta' => 3 + ($index % 6),
                'tahapan' => $tahapan,
                'catatan' => $tahapan === 'Dibatalkan' ? 'Dibatalkan karena jadwal bentrok.' : 'Menunggu konfirmasi instruktur.',
            ];
        })->all();
    }
}
