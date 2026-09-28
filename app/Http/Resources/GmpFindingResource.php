<?php

namespace App\Http\Resources;

use App\Models\GmpFindingEvidence;
use App\Models\GmpFindingHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Temuan GMP. Evidence & timeline hanya disertakan bila relasinya di-load (detail drawer). */
class GmpFindingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'finding_code' => $this->finding_code,
            'source_type' => $this->source_type,
            'assessment_id' => $this->assessment_id,
            'year' => $this->year,
            'week' => $this->week,
            'area_id' => $this->area_id,
            'location_id' => $this->location_id,
            'aspect_id' => $this->aspect_id,
            'checklist_id' => $this->checklist_id,
            'standard_description' => $this->standard_description,
            'finding_description' => $this->finding_description,
            'recommendation' => $this->recommendation,
            'priority' => $this->priority,
            'pic_user_id' => $this->pic_user_id,
            'pic_nama' => $this->pic?->name,
            'observer' => $this->observer,
            'finding_date' => $this->finding_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'progress_percentage' => $this->progress_percentage,
            'root_cause' => $this->root_cause,
            'action_plan' => $this->action_plan,
            'actual_action' => $this->actual_action,
            'obstacle' => $this->obstacle,
            'started_at' => $this->started_at?->toDateString(),
            'completed_at' => $this->completed_at?->toDateString(),
            'verified_by' => $this->verified_by,
            'verified_at' => $this->verified_at?->toDateTimeString(),
            'verification_note' => $this->verification_note,
            'status' => $this->status->value,
            'computed_status' => $this->computedStatus(),
            'created_by' => $this->created_by,
            'evidence' => $this->whenLoaded('evidences', fn () => $this->groupedEvidence()),
            'timeline' => $this->whenLoaded('histories', fn () => $this->histories->map(fn (GmpFindingHistory $h) => [
                'id' => $h->id,
                'created_at' => $h->created_at?->toDateTimeString(),
                'aktor_nama' => $h->actor?->name ?? $h->created_by,
                'action_type' => $h->action_type,
                'description' => $h->description,
                'new_status' => $h->new_status,
            ])),
        ];
    }

    private function groupedEvidence(): array
    {
        $grouped = collect(GmpFindingEvidence::TYPES)->mapWithKeys(fn ($type) => [$type => []])->all();
        foreach ($this->evidences as $ev) $grouped[$ev->evidence_type][] = $ev->file_path;

        return [...$grouped, 'before' => $grouped['before'][0] ?? null];
    }
}
