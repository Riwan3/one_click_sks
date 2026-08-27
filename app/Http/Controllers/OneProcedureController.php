<?php

namespace App\Http\Controllers;

use App\Support\MenuAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OneProcedureController extends Controller
{
    private const PER_PAGE = 10;

    private const STATUS_OPTIONS = ['Aktif', 'Draft', 'Review'];

    private const KATEGORI = ['Operasional Tambang', 'K3', 'Perawatan Unit', 'Logistik', 'Lingkungan'];

    private const NAMA_PROSEDUR = [
        'Prosedur Pengisian Bahan Bakar', 'Prosedur Pemeriksaan Unit Harian', 'Prosedur Evakuasi Darurat',
        'Prosedur Loading & Hauling', 'Prosedur Penanganan Tumpahan Oli', 'Prosedur Isolasi Energi (LOTO)',
        'Prosedur Inspeksi Jalan Tambang', 'Prosedur Pergantian Shift', 'Prosedur Pengelolaan Limbah B3',
        'Prosedur Penggunaan APD', 'Prosedur Blasting', 'Prosedur Dewatering Pit',
    ];

    /**
     * Dummy data — ganti dengan query database (master prosedur operasional) begitu backend One-Procedure siap.
     */
    public function dashboard(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $page = max(1, (int) $request->integer('page', 1));

        $rows = collect($this->dummyRows())
            ->when($search !== '', fn ($items) => $items->filter(
                fn (array $row) => Str::contains(strtolower($row['namaProsedur']), strtolower($search))
                    || Str::contains(strtolower($row['kategori']), strtolower($search))
            ))
            ->values();

        $total = $rows->count();
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min($page, $lastPage);

        return Inertia::render('OneProcedure/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => [
                'signature' => 2,
                'notification' => 3,
                'message' => 1,
            ],
            'menu' => MenuAccess::forConfig('one_procedure_menu'),
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
        $review = collect($rows)->where('status', 'Review')->count();

        return [
            'totalProsedur' => $total,
            'totalAktif' => $aktif,
            'totalReview' => $review,
            'totalKategori' => count(self::KATEGORI),
        ];
    }

    private function dummyRows(): array
    {
        return collect(self::NAMA_PROSEDUR)->map(fn (string $nama, int $index) => [
            'id' => $index + 1,
            'kodeProsedur' => sprintf('SOP-%03d', $index + 1),
            'namaProsedur' => $nama,
            'kategori' => self::KATEGORI[$index % count(self::KATEGORI)],
            'tanggalUpdate' => now()->subMonths($index % 12)->format('Y-m-d'),
            'status' => self::STATUS_OPTIONS[$index % 5 === 4 ? 2 : ($index % 4 === 3 ? 1 : 0)],
        ])->all();
    }
}
