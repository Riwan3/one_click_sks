<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OneAdminController extends Controller
{
    private const PER_PAGE = 10;

    private const STATUS_OPTIONS = ['Aktif', 'Cuti', 'Resign'];

    private const NAMES = ['Ahmad Fauzi', 'Budi Santoso', 'Candra Wijaya', 'Dedi Kurniawan', 'Eko Prasetyo', 'Fajar Nugroho', 'Gilang Ramadhan', 'Hendra Saputra', 'Irfan Hakim', 'Joko Susilo', 'Wahyu Ananda', 'Yusuf Maulana'];

    private const DEPARTEMEN = ['Produksi', 'HRD', 'Finance', 'Plant', 'Logistik'];

    private const JABATAN = ['Staff', 'Supervisor', 'Group Leader', 'Admin', 'Koordinator'];

    /**
     * Dummy data — ganti dengan query database (database personal karyawan) begitu backend One-Admin siap.
     */
    public function dashboard(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $page = max(1, (int) $request->integer('page', 1));

        $rows = collect($this->dummyRows())
            ->when($search !== '', fn ($items) => $items->filter(
                fn (array $row) => Str::contains(strtolower($row['nama']), strtolower($search))
                    || Str::contains(strtolower($row['departemen']), strtolower($search))
            ))
            ->values();

        $total = $rows->count();
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min($page, $lastPage);

        return Inertia::render('OneAdmin/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => config('one_admin_menu'),
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
        $aktif = collect($rows)->where('status', 'Aktif')->count();
        $cuti = collect($rows)->where('status', 'Cuti')->count();

        return [
            'totalKaryawan' => $total,
            'totalAktif' => $aktif,
            'totalCuti' => $cuti,
            'totalDepartemen' => count(self::DEPARTEMEN),
        ];
    }

    private function dummyRows(): array
    {
        return collect(self::NAMES)->map(fn (string $nama, int $index) => [
            'id' => $index + 1,
            'nama' => $nama,
            'nik' => (string) (30001 + $index),
            'departemen' => self::DEPARTEMEN[$index % count(self::DEPARTEMEN)],
            'jabatan' => self::JABATAN[$index % count(self::JABATAN)],
            'tanggalBergabung' => now()->subYears(1 + $index % 5)->subMonths($index)->format('Y-m-d'),
            'status' => self::STATUS_OPTIONS[$index % 6 === 5 ? 2 : ($index % 4 === 3 ? 1 : 0)],
        ])->all();
    }
}
