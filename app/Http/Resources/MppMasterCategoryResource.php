<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MppMasterCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'kode' => $this->kode,
            'plan_mp' => $this->plan_mp,
            'sort_order' => $this->sort_order,
            'active' => $this->active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'creator' => $this->whenLoaded('creator', $this->creator?->name),
            'updater' => $this->whenLoaded('updater', $this->updater?->name),
        ];
    }
}
