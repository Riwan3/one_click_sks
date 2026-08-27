<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MorDataResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employeeId' => $this->employee_id,
            'nrp' => $this->whenLoaded('employee', fn () => $this->employee?->nrp),
            'nama' => $this->whenLoaded('employee', fn () => $this->employee?->name),
            'jabatan' => $this->whenLoaded('employee', fn () => $this->employee?->jabatan_ss6),
            'posisi' => $this->posisi,
            'glName' => $this->gl_name,
            'bulan' => (int) $this->bulan,
            'tahun' => (int) $this->tahun,
            'atrPersen' => (float) $this->atr_persen,
            'atrPoint' => (float) $this->atr_point,
            'workDay' => (int) $this->work_day,
            'sakit' => (int) $this->sakit,
            'ijin' => (int) $this->ijin,
            'alpa' => (int) $this->alpa,
            'timesheetPersen' => (float) $this->timesheet_persen,
            'timesheetPoint' => (float) $this->timesheet_point,
            'hmRatio' => (float) $this->hm_ratio,
            'hmPoint' => (float) $this->hm_point,
            'hmJam' => (float) $this->hm_jam,
            'hmTarget' => (float) $this->hm_target,
            'fuelPersen' => (float) $this->fuel_persen,
            'fuelPoint' => (float) $this->fuel_point,
            'fuelActual' => (float) $this->fuel_actual,
            'fuelLimit' => (float) $this->fuel_limit,
            'pdtyPersen' => $this->pdty_persen !== null ? (float) $this->pdty_persen : null,
            'pdtyPoint' => $this->pdty_point !== null ? (float) $this->pdty_point : null,
            'actCoal' => $this->act_coal !== null ? (float) $this->act_coal : null,
            'targtCoal' => $this->targt_coal !== null ? (float) $this->targt_coal : null,
            'actOb' => $this->act_ob !== null ? (float) $this->act_ob : null,
            'targtOb' => $this->targt_ob !== null ? (float) $this->targt_ob : null,
            'pembinaan' => (int) $this->pembinaan,
            'pembinaanPoin' => (float) $this->pembinaan_poin,
            'ketPembinaan' => $this->ket_pembinaan,
            'jumlahPoint' => (float) $this->jumlah_point,
            'totalPoin' => (float) $this->total_poin,
            'ranking' => $this->ranking,
            'dariOperator' => $this->dari_operator,
            'isProduksi' => $this->isProduksi(),
            'targets' => $this->targets(),
        ];
    }
}
