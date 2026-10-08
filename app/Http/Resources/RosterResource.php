<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Baris v2_rosters (array dari RosterV2Service) -> kontrak data ke frontend. */
class RosterResource extends JsonResource
{
    public const STATUS_LABELS = ['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'];

    public function toArray(Request $request): array
    {
        $r = $this->resource;

        return [
            'id' => (int) $r['id'],
            'name' => $r['name'],
            'roster_group' => $r['roster_group'],
            'period_start' => $r['period_start'],
            'period_end' => $r['period_end'],
            'status' => $r['status'],
            'status_label' => self::STATUS_LABELS[$r['status']] ?? $r['status'],
            'notes' => $r['notes'] ?? null,
            'pic_name' => $r['pic_name'] ?? null,
            'pic_jabatan' => $r['pic_jabatan'] ?? null,
            'visible_months' => $r['visible_months'] ?? null,
            'created_at' => $r['created_at'] ?? null,
            'updated_at' => $r['updated_at'] ?? null,
        ];
    }
}
