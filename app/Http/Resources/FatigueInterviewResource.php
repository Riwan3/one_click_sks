<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FatigueInterviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getRouteKey(),
            'rawId' => $this->id,
            'hashid' => $this->getRouteKey(),
            'noSurat' => $this->no_surat,
            'employeeId' => $this->employee_id,
            'nrp' => $this->whenLoaded('employee', fn () => $this->employee?->nrp) ?? $this->nrp_operator,
            'nama' => $this->whenLoaded('employee', fn () => $this->employee?->name) ?? $this->nama_operator,
            'nomorUnit' => $this->nomor_unit,
            'area' => $this->area,
            'nrpOperator' => $this->nrp_operator ?? $this->whenLoaded('employee', fn () => $this->employee?->nrp),
            'namaOperator' => $this->nama_operator ?? $this->whenLoaded('employee', fn () => $this->employee?->name),
            'pengawasId' => $this->pengawas_id,
            'nrpPengawas' => $this->nrp_pengawas ?? $this->whenLoaded('pengawas', fn () => $this->pengawas?->nrp),
            'namaPengawas' => $this->nama_pengawas ?? $this->whenLoaded('pengawas', fn () => $this->pengawas?->name),
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'jam' => $this->jam,
            'q1ShiftDay' => $this->q1_shift_day,
            'q2BodyCondition' => $this->q2_body_condition,
            'q3Reason' => $this->q3_reason,
            'q3Background' => $this->q3_background,
            'q4RestHours' => $this->q4_rest_hours,
            'q5RestQuality' => $this->q5_rest_quality,
            'q5BadQualityReason' => $this->q5_bad_quality_reason,
            'q6HomeComfort' => $this->q6_home_comfort,
            'q6DiscomfortReason' => $this->q6_discomfort_reason,
            'q7OtherActivities' => $this->q7_other_activities,
            'q7ActivityDetails' => $this->q7_activity_details,
            'q8Education' => $this->q8_education,
            'q9HeavyFatigueDay' => $this->q9_heavy_fatigue_day,
            'q10FitPlan' => $this->q10_fit_plan,
            'q11Suggestions' => $this->q11_suggestions,
            'ttdPengawas' => $this->ttd_pengawas,
            'createdByName' => $this->whenLoaded('creator', fn () => $this->creator?->name) ?? $this->created_by,
            'updatedByName' => $this->whenLoaded('updater', fn () => $this->updater?->name) ?? $this->updated_by,
            'createdAt' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
