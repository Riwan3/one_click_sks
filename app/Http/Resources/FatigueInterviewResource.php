<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FatigueInterviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'noSurat' => $this->no_surat,
            'employeeId' => $this->employee_id,
            'nrp' => $this->whenLoaded('employee', fn () => $this->employee?->nrp),
            'nama' => $this->whenLoaded('employee', fn () => $this->employee?->name),
            'nomorUnit' => $this->nomor_unit,
            'area' => $this->area,
            'nrpOperator' => $this->nrp_operator,
            'namaOperator' => $this->nama_operator,
            'pengawasId' => $this->pengawas_id,
            'nrpPengawas' => $this->nrp_pengawas,
            'namaPengawas' => $this->whenLoaded('pengawas', fn () => $this->pengawas?->name) ?? $this->nama_pengawas,
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
            'createdByName' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'createdAt' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
