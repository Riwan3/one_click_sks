<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Satu CSA beserta kategori & unit (hasil ManpowerV201Repository::tree) untuk halaman Master CSA. */
class ManpowerV201MasterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this['id'],
            'name' => $this['name'],
            'is_locked' => (bool) $this['is_locked'],
            'categories' => collect($this['categories'])->map(fn ($cat) => [
                'id' => (int) $cat['id'], 'name' => $cat['name'], 'col_pos' => (int) $cat['col_pos'],
                'units' => collect($cat['units'])->map(fn ($u) => [
                    'id' => (int) $u['id'], 'unit' => $u['unit'],
                    'is_spare_slot' => (bool) $u['is_spare_slot'], 'is_high_risk' => (bool) $u['is_high_risk'],
                ])->values(),
            ])->values(),
        ];
    }
}
