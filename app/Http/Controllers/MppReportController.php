<?php

namespace App\Http\Controllers;

use App\Models\MppDailyAdjustment;
use App\Models\MppKesiapanOperator;
use App\Models\MppRosterData;
use App\Models\MppUnitPhoto;
use App\Services\MppPhotoService;
use App\Services\MppReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MppReportController extends Controller
{
    public function __construct(
        private MppReportService $reportService,
        private MppPhotoService $photoService
    ) {}

    public function exportDailyAtr(Request $request): StreamedResponse
    {
        $this->authorize('view', MppDailyAdjustment::class);

        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'shift' => 'nullable|in:D,N',
            'kategori' => 'nullable|string',
        ]);

        $data = $this->reportService->generateDailyAtrReport($filters);

        return $this->generateExcelResponse($data, 'Daily_ATR_' . now()->format('YmdHis'));
    }

    public function exportActualMpp(Request $request): StreamedResponse
    {
        $this->authorize('view', MppDailyAdjustment::class);

        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'shift' => 'nullable|in:D,N',
            'kategori' => 'nullable|string',
        ]);

        $data = $this->reportService->generateActualMppReport($filters);

        return $this->generateExcelResponse($data, 'Actual_MPP_' . now()->format('YmdHis'));
    }

    public function exportCheckpoint(Request $request): StreamedResponse
    {
        $this->authorize('view', MppUnitPhoto::class);

        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'unit_code' => 'nullable|string',
            'shift' => 'nullable|in:D,N',
        ]);

        $query = MppUnitPhoto::byJenis('Checkpoint');

        if ($filters['date_from'] ?? false) {
            $query->where('tanggal', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] ?? false) {
            $query->where('tanggal', '<=', $filters['date_to']);
        }
        if ($filters['unit_code'] ?? false) {
            $query->byUnit($filters['unit_code']);
        }
        if ($filters['shift'] ?? false) {
            $query->byShift($filters['shift']);
        }

        $photos = $query->latest('tanggal')->get();

        $data = $photos->map(fn ($photo) => [
            'Tanggal' => $photo->tanggal->format('Y-m-d'),
            'Shift' => $photo->shift,
            'Nama Operator' => $photo->nama,
            'Unit' => $photo->unit_code,
            'Jenis' => $photo->jenis,
        ]);

        return $this->generateExcelResponse($data, 'Checkpoint_' . now()->format('YmdHis'));
    }

    public function exportOjk(Request $request): StreamedResponse
    {
        $this->authorize('view', MppUnitPhoto::class);

        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'csa_code' => 'nullable|string',
            'shift' => 'nullable|in:D,N',
        ]);

        $query = MppUnitPhoto::byJenis('OJK');

        if ($filters['date_from'] ?? false) {
            $query->where('tanggal', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] ?? false) {
            $query->where('tanggal', '<=', $filters['date_to']);
        }
        if ($filters['csa_code'] ?? false) {
            $query->byUnit($filters['csa_code']);
        }
        if ($filters['shift'] ?? false) {
            $query->byShift($filters['shift']);
        }

        $photos = $query->latest('tanggal')->get();

        $data = $photos->map(fn ($photo) => [
            'Tanggal' => $photo->tanggal->format('Y-m-d'),
            'Shift' => $photo->shift,
            'Nama Operator' => $photo->nama,
            'CSA Code' => $photo->unit_code,
            'Jenis' => $photo->jenis,
        ]);

        return $this->generateExcelResponse($data, 'OJK_' . now()->format('YmdHis'));
    }

    public function exportKesiapanTrend(Request $request): StreamedResponse
    {
        $this->authorize('view', MppKesiapanOperator::class);

        $days = $request->input('days', 30);

        $trend = $this->reportService->generateKesiapanTrend($days);

        return $this->generatePdfResponse($trend, 'Kesiapan_Trend_' . now()->format('YmdHis'));
    }

    public function exportEmployeePhotos($employeeId): StreamedResponse
    {
        $this->authorize('downloadFiles', auth()->user());

        $zipPath = $this->photoService->zipPhotosForEmployee($employeeId);

        return response()->download($zipPath, 'employee_photos_' . $employeeId . '.zip')->deleteFileAfterSend();
    }

    private function generateExcelResponse($data, string $filename): StreamedResponse
    {
        return response()->stream(function () use ($data) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, array_keys($data->first() ?? []));

            foreach ($data as $row) {
                fputcsv($stream, $row);
            }

            fclose($stream);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }

    private function generatePdfResponse($data, string $filename): StreamedResponse
    {
        // This is a placeholder for PDF generation
        // In production, use barryvdh/laravel-dompdf
        return $this->generateExcelResponse(collect($data), $filename);
    }
}
