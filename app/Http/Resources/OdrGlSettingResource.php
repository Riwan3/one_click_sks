<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OdrGlSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'area' => $this->area,
            'unit' => $this->unit,
            'front_pic' => $this->front_pic,
            'disposal_pic' => $this->disposal_pic,
        ];
    }
}
