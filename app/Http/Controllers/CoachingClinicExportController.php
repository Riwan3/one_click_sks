<?php

namespace App\Http\Controllers;

use App\Models\CoachingClinic;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CoachingClinicExportController extends Controller
{
    public function export(Request $request)
    {
        $format = $request->input('format', 'excel');
        $dateFrom = $request->input('date_from', now()->subMonths(1)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $department = $request->input('department');

        $query = CoachingClinic::query()->published();

        $query->whereBetween('tanggal', [$dateFrom, $dateTo]);

        if ($department) {
            $query->whereHas('employee', function ($q) use ($department) {
                $q->where('departemen', $department);
            });
        }

        $clinics = $query->with(['employee', 'coach'])->get();

        if ($format === 'pdf') {
            return $this->exportPdf($clinics, $dateFrom, $dateTo);
        }

        return $this->exportExcel($clinics, $dateFrom, $dateTo);
    }

    private function exportPdf($clinics, $dateFrom, $dateTo)
    {
        $data = [
            'clinics' => $clinics,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'generatedAt' => now()->format('d-m-Y H:i:s'),
        ];

        $pdf = Pdf::loadView('coaching-clinic-report-pdf', $data);

        return $pdf->download('coaching-clinic-report-' . now()->format('Y-m-d') . '.pdf');
    }

    private function exportExcel($clinics, $dateFrom, $dateTo)
    {
        $data = [];
        foreach ($clinics as $clinic) {
            $data[] = [
                'NRP' => $clinic->employee?->nrp ?? '-',
                'Nama' => $clinic->employee?->nama ?? '-',
                'Tanggal' => $clinic->tanggal?->format('d-m-Y') ?? '-',
                'Coach' => $clinic->coach?->name ?? '-',
                'Materi' => implode(', ', array_filter([
                    $clinic->materi_pribadi ? 'Pribadi' : '',
                    $clinic->materi_keluarga ? 'Keluarga' : '',
                    $clinic->materi_lingkungan ? 'Lingkungan' : '',
                    $clinic->materi_pekerjaan ? 'Pekerjaan' : '',
                ])),
                'Status' => $clinic->status ?? 'Published',
            ];
        }

        return Excel::download(
            new \App\Exports\CoachingClinicExport($data),
            'coaching-clinic-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}

