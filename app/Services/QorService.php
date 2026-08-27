<?php

namespace App\Services;

use App\Imports\QorDprImport;
use App\Models\QorDpr;
use App\Models\QorPerforma;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class QorService
{
    private const QUARTAL_MONTHS = [
        1 => [1, 3], 2 => [4, 6], 3 => [7, 9], 4 => [10, 12],
    ];

    public function importDpr(UploadedFile $file): array
    {
        $import = new QorDprImport;

        Excel::import($import, $file);

        return [
            'imported' => $import->importedCount(),
            'skipped' => $import->skippedCount(),
        ];
    }

    public function calculateQuarterlyPerforma(int $year, int $quarter): void
    {
        [$start, $end] = self::quartalRange($year, $quarter);

        $aggregates = QorDpr::query()
            ->selectRaw('employee_id, MAX(loader) as loader, ROUND(AVG(jarak), 1) as jarak, MAX(kode_unit) as kode_unit, SUM(rit) as rit_total, SUM(total_bcm) as bcm_total')
            ->whereBetween('tanggal', [$start, $end])
            ->groupBy('employee_id')
            ->get();

        foreach ($aggregates as $row) {
            QorPerforma::updateOrCreate(
                ['employee_id' => $row->employee_id, 'tahun' => $year, 'quartal' => $quarter],
                [
                    'loader' => $row->loader,
                    'jarak' => $row->jarak,
                    'kode_unit' => $row->kode_unit,
                    'rit_total' => $row->rit_total,
                    'bcm_total' => $row->bcm_total,
                ]
            );
        }

        $this->refreshRanking($year, $quarter);
    }

    public function getBcmRanking(int $year, int $quarter): Collection
    {
        return QorPerforma::query()
            ->with('employee')
            ->period($year, $quarter)
            ->orderBy('ranking')
            ->get();
    }

    public static function quartalRange(int $year, int $quarter): array
    {
        [$startMonth, $endMonth] = self::QUARTAL_MONTHS[$quarter] ?? self::QUARTAL_MONTHS[1];

        $start = sprintf('%d-%02d-01', $year, $startMonth);
        $end = date('Y-m-t', strtotime(sprintf('%d-%02d-01', $year, $endMonth)));

        return [$start, $end];
    }

    private function refreshRanking(int $year, int $quarter): void
    {
        $ranked = QorPerforma::query()
            ->period($year, $quarter)
            ->orderByDesc('bcm_total')
            ->orderByDesc('rit_total')
            ->pluck('id');

        foreach ($ranked as $index => $id) {
            QorPerforma::where('id', $id)->update([
                'ranking' => $index + 1,
                'dari_operator' => $ranked->count(),
            ]);
        }
    }
}
