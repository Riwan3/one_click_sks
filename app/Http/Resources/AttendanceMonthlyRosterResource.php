<?php

namespace App\Http\Resources;

use App\Models\AttendanceMonthlyRoster;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceMonthlyRosterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $codes = collect(AttendanceMonthlyRoster::CODE_COLUMNS)
            ->mapWithKeys(fn (string $code) => [$code => (int) $this->{$code}])
            ->all();

        return array_merge([
            'id' => $this->id,
            'employeeId' => $this->employee_id,
            'nrp' => $this->whenLoaded('employee', fn () => $this->employee?->nrp),
            'nama' => $this->whenLoaded('employee', fn () => $this->employee?->name),
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'dept' => $this->dept,
            'jabatan' => $this->jabatan,
            'posisi' => $this->posisi,
            'comp' => $this->comp,
        ], $codes, [
            'atrPercent' => $this->atr_percent,
        ]);
    }
}
