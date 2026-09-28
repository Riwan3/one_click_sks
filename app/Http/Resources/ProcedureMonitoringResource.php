<?php

namespace App\Http\Resources;

use App\Enums\ProcedureMonitoringStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/** Satu baris tabel Monitoring Evaluasi Prosedur (row hasil query builder). */
class ProcedureMonitoringResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = ProcedureMonitoringStatus::resolve($this->resource);

        return [
            'assessment_id' => $this->assessment_id,
            'evaluation_id' => $this->evaluation_id,
            'nrp' => $this->nrp,
            'name' => $this->name ?? 'NRP '.$this->nrp,
            'jabatan' => $this->jabatan,
            'assessment_title' => $this->assessment_title,
            'passing_grade' => (int) $this->passing_grade,
            'score' => $this->score === null ? null : (float) $this->score,
            'attempt' => $this->attempt ? (int) $this->attempt : null,
            'status' => $status->value,
            'status_label' => $status->label(),
            'completed_at' => $this->completed_at ? Carbon::parse($this->completed_at)->format('d M Y') : null,
        ];
    }
}
