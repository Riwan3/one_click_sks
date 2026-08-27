<?php

namespace App\Http\Controllers;

use App\Http\Resources\QorPerformaResource;
use App\Services\QorService;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuartalOperatorReportController extends Controller
{
    public function __construct(private QorService $qorService) {}

    public function index(Request $request): Response
    {
        $quartal = (int) $request->integer('quartal', (int) ceil(now()->month / 3));
        $quartal = $quartal >= 1 && $quartal <= 4 ? $quartal : 1;
        $tahun = (int) $request->integer('tahun', now()->year);

        $ranking = $this->qorService->getBcmRanking($tahun, $quartal);

        return Inertia::render('OnePower/QuartalOperatorReport/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'isAdmin' => (bool) $request->user()?->isAdmin(),
            'filters' => [
                'quartal' => $quartal,
                'tahun' => $tahun,
                'years' => range(now()->year - 2, now()->year),
            ],
            'ranking' => QorPerformaResource::collection($ranking)->resolve(),
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $result = $this->qorService->importDpr($request->file('file'));

        return redirect()->back()->with(
            'success',
            "Berhasil mengimport {$result['imported']} data DPR ({$result['skipped']} baris dilewati). Jalankan Recalculate untuk memperbarui ranking BCM."
        );
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = ['tanggal', 'operator_ex', 'operator_hd', 'loader', 'jarak', 'kode_unit', 'rit', 'total_bcm'];

        return response()->streamDownload(function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        }, 'qor-dpr-template.csv', [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    public function recalculate(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $tahun = (int) $request->integer('tahun', now()->year);
        $quartal = (int) $request->integer('quartal', (int) ceil(now()->month / 3));

        $this->qorService->calculateQuarterlyPerforma($tahun, $quartal);

        return redirect()->back()->with('success', 'Ranking BCM kuartal berhasil dihitung ulang.');
    }

    private function notifCounts(): array
    {
        return ['signature' => 2, 'notification' => 3, 'message' => 1];
    }
}
