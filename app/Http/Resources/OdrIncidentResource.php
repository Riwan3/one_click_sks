<?php

namespace App\Http\Resources;

use App\Enums\OdrIncidentActionType;
use App\Models\OdrIncidentAction;
use App\Models\OdrIncidentEvidence;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** Insiden beserta tindakan pencegahan / mitigasi dan evidence-nya. */
class OdrIncidentResource extends JsonResource
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal_insiden' => $this->tanggal_insiden?->toDateString(),
            'judul_insiden' => $this->judul_insiden,
            'area' => $this->area,
            'deskripsi_insiden' => $this->deskripsi_insiden,
            'target_selesai' => $this->target_selesai?->toDateString(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'is_overdue' => $this->isOverdue(),
            'pencegahan' => $this->actionsOf(OdrIncidentActionType::Pencegahan),
            'mitigasi' => $this->actionsOf(OdrIncidentActionType::Mitigasi),
        ];
    }

    private function actionsOf(OdrIncidentActionType $type): array
    {
        return $this->actions->where('jenis', $type)->values()->map(fn (OdrIncidentAction $action) => [
            'id' => $action->id,
            'uraian_tindakan' => $action->uraian_tindakan,
            'pic' => $action->pic,
            'evidence' => $action->evidences->map(fn (OdrIncidentEvidence $ev) => [
                'id' => $ev->id,
                'url' => Storage::disk('public')->url($ev->file_path),
                'name' => $ev->original_name ?: 'File',
                'is_image' => in_array(strtolower(pathinfo($ev->original_name ?: $ev->file_path, PATHINFO_EXTENSION)), self::IMAGE_EXTENSIONS, true),
            ])->all(),
        ])->all();
    }
}
