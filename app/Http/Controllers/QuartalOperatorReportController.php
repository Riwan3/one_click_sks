<?php

namespace App\Http\Controllers;

use App\Services\QorService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
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
        $quartal = ($quartal >= 1 && $quartal <= 4) ? $quartal : 1;
        $tahun = (int) $request->integer('tahun', now()->year);
        $tab = $request->string('tab', 'operator_report')->toString();
        $search = $request->string('search', '')->toString();
        $kategori = $request->string('kategori', 'ALL')->toString();

        $user = $request->user();
        $isGroupLeader = $user && $user->isGroupLeader() && ! $user->isAdmin();
        $glId = $isGroupLeader ? $user->getEmployeeRecord()?->id : null;

        if ($isGroupLeader) {
            $tab = 'monthly_report';
        }

        $data = match ($tab) {
            'monthly_report' => $this->qorService->getMonthlyReport($tahun, $quartal, $search, glId: $glId),
            'quartal_report' => $this->qorService->getQuartalRanking($tahun, $quartal, $search),
            default => $this->qorService->getOperatorReport($tahun, $quartal, $search, $kategori),
        };

        return Inertia::render('OnePower/QuartalOperatorReport/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'isAdmin' => (bool) $user?->isAdmin(),
            'isGroupLeader' => $isGroupLeader,
            'activeTab' => $tab,
            'filters' => [
                'quartal' => $quartal,
                'tahun' => $tahun,
                'search' => $search,
                'kategori' => $kategori,
                'years' => range(now()->year - 3, now()->year + 1),
            ],
            'reportData' => $data,
        ]);
    }

    public function getDprDaily(Request $request): JsonResponse
    {
        $nrp = $request->string('nrp')->toString();
        $quartal = (int) $request->integer('quartal', 1);
        $tahun = (int) $request->integer('tahun', now()->year);

        if (!$nrp) {
            return response()->json(['success' => false, 'message' => 'NRP wajib diisi'], 400);
        }

        $daily = $this->qorService->getDprDaily($nrp, $tahun, $quartal);

        return response()->json([
            'success' => true,
            'data' => $daily,
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt'],
            'type' => ['required', 'string', 'in:dpr_ob,dpr_coal,sap_hm,atr'],
        ]);

        $type = $request->input('type');
        $typeLabels = [
            'dpr_ob' => 'DPR OB',
            'dpr_coal' => 'DPR COAL',
            'sap_hm' => 'SAP HM',
            'atr' => 'ATR Attendance',
        ];

        try {
            $result = $this->qorService->importFile($request->file('file'), $type);
            $label = $typeLabels[$type] ?? $type;

            return redirect()->back()->with(
                'success',
                "Berhasil import data {$label}: {$result['imported']} baris tersimpan ({$result['skipped']} baris dilewati)."
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import file: ' . $e->getMessage());
        }
    }

    public function recalculate(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $tahun = (int) $request->integer('tahun', now()->year);
        $quartal = (int) $request->integer('quartal', (int) ceil(now()->month / 3));

        $res = $this->qorService->runRecalculation($tahun, $quartal);

        return redirect()->back()->with(
            'success',
            "Kalkulasi berhasil: {$res['pdty']['processed']} data PDTY & {$res['mor']['total']} ranking MOR berhasil diperbarui untuk Kuartal {$quartal} Tahun {$tahun}."
        );
    }

    public function reset(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $tahun = (int) $request->integer('tahun', now()->year);
        $quartal = (int) $request->integer('quartal', 1);
        $resetAll = $request->boolean('reset_all', false);

        $this->qorService->resetPeriod($tahun, $quartal, $resetAll);

        $msg = $resetAll
            ? 'Seluruh data QOR & MOR berhasil di-reset bersih.'
            : "Data periode Kuartal {$quartal} Tahun {$tahun} berhasil di-reset.";

        return redirect()->back()->with('success', $msg);
    }

    public function downloadTemplate(Request $request): StreamedResponse
    {
        $type = $request->string('type', 'dpr_ob')->toString();

        $headers = match ($type) {
            'dpr_coal' => ['TANGGAL', 'OPERATOR_EX', 'OPERATOR_HD', 'LOADER', 'JARAK', 'KODE_UNIT', 'RIT', 'TOTAL_TON'],
            'sap_hm' => ['MEASUREMENT DOC', 'OPERATOR', 'NAMA', 'KODE UNIT', 'ALAT MUAT', 'TANGGAL', 'HOUR TIME', 'AKTIVITAS'],
            'atr' => ['NRP', 'NAME', 'POSITION', 'DATE', 'CODE', 'DEPT', 'COMPANY'],
            default => ['TANGGAL', 'OPERATOR_EX', 'OPERATOR_HD', 'LOADER', 'JARAK', 'KODE_UNIT', 'RIT', 'TOTAL_BCM'],
        };

        $filename = "template-{$type}.csv";

        return response()->streamDownload(function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }
}
