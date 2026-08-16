<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OneSettingController extends Controller
{
    private const PER_PAGE = 10;

    private const STATUS_OPTIONS = ['Siap', 'Tidak Siap', 'Perlu Review'];

    private const OPERATORS = ['Ahmad Fauzi', 'Budi Santoso', 'Candra Wijaya', 'Dedi Kurniawan', 'Eko Prasetyo', 'Fajar Nugroho', 'Gilang Ramadhan', 'Hendra Saputra', 'Irfan Hakim', 'Joko Susilo', 'Wahyu Ananda', 'Yusuf Maulana'];

    private const UNITS = ['HD785-012', 'PC2000-004', 'D375-007', 'WA500-009', 'GD675-002', 'HD785-018'];

    private const SHIFTS = ['Shift 1', 'Shift 2', 'Shift 3'];

    /**
     * Dummy data — ganti dengan query database (setting manpower/fleet) begitu backend One-Setting siap.
     */
    public function dashboard(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $page = max(1, (int) $request->integer('page', 1));

        $rows = collect($this->dummyRows())
            ->when($search !== '', fn ($items) => $items->filter(
                fn (array $row) => Str::contains(strtolower($row['namaOperator']), strtolower($search))
                    || Str::contains(strtolower($row['noUnit']), strtolower($search))
            ))
            ->values();

        $total = $rows->count();
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min($page, $lastPage);

        return Inertia::render('OneSetting/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => [
                'signature' => 2,
                'notification' => 3,
                'message' => 1,
            ],
            'menu' => config('one_setting_menu'),
            'search' => $search,
            'stats' => $this->statsFrom($rows->all()),
            'rows' => $rows->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values()->all(),
            'pagination' => [
                'currentPage' => $page,
                'lastPage' => $lastPage,
                'total' => $total,
            ],
        ]);
    }

    private function statsFrom(array $rows): array
    {
        $total = count($rows);
        $siap = collect($rows)->where('status', 'Siap')->count();
        $tidakSiap = collect($rows)->where('status', 'Tidak Siap')->count();

        return [
            'totalOperator' => $total,
            'totalSiap' => $siap,
            'totalTidakSiap' => $tidakSiap,
            'totalUnitTerkonfigurasi' => count(self::UNITS),
        ];
    }

    private function dummyRows(): array
    {
        return collect(self::OPERATORS)->map(fn (string $nama, int $index) => [
            'id' => $index + 1,
            'namaOperator' => $nama,
            'nrp' => (string) (20001 + $index),
            'noUnit' => self::UNITS[$index % count(self::UNITS)],
            'shift' => self::SHIFTS[$index % count(self::SHIFTS)],
            'status' => self::STATUS_OPTIONS[$index % 4 === 3 ? 2 : $index % 2],
            'keterangan' => $index % 4 === 3 ? 'Menunggu verifikasi dokumen' : '-',
        ])->all();
    }
}
