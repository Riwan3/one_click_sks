<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/** Assignment harian beserta checklist task & compliance (Daily Compliance / Assignment Harian). */
class OdrAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $compliance = $this->compliance();

        return [
            'id' => $this->id,
            'tanggal' => Carbon::parse($this->tanggal)->toDateString(),
            'shift' => $this->shift ?: 'Shift 1',
            'area' => $this->area,
            'unit' => $this->unit,
            'pengawas' => $this->pengawas,
            'keterangan' => $this->keterangan,
            'status' => $this->status,
            'tasks' => $this->tasks(),
            'done' => $this->doneFlags(),
            'compliance' => $compliance,
            'report_pct' => $this->compliancePercent(),
        ];
    }
}
