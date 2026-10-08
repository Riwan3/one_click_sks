<?php

namespace App\Http\Resources;

use App\Services\RosterV2Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Tampilan matriks satu bulan (RosterV2Service::getMatrix) -> kontrak data ke frontend. */
class RosterMatrixResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $m = $this->resource;

        return [
            'month' => $m['month'],
            'months' => $m['months'],
            'days' => $m['days'],
            'month_from' => $m['month_from'],
            'month_to' => $m['month_to'],
            'ctx' => $m['ctx'],
            'summary_codes' => RosterV2Service::MATRIX_SUMMARY,
            'shifts' => array_map(fn (array $s) => ['code' => $s['code'], 'label' => $s['label']], $m['shifts']),
            'sections' => array_map(fn (array $s) => [
                'id' => $s['id'],
                'name' => $s['name'],
                'total' => $s['total'],
                'targets' => $s['targets'],
                'members' => array_map(fn (array $mb) => $this->member($mb), $s['members']),
            ], $m['sections']),
            'loose' => $m['loose'],
        ];
    }

    private function member(array $mb): array
    {
        return [
            'id' => (int) $mb['id'],
            'nrp' => $mb['nrp'],
            'employee_name' => $mb['employee_name'],
            'unit' => $mb['unit'],
            'kode_off' => $mb['kode_off'],
            'work_pattern' => $mb['work_pattern'] ?? 'ROTASI',
            'cells' => $mb['cells'],
        ];
    }
}
