<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Satu baris riwayat perubahan roster (v2_roster_changelog). */
class RosterChangelogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $r = $this->resource;

        return [
            'id' => (int) $r['id'],
            'actor_nrp' => $r['actor_nrp'],
            'actor_name' => $r['actor_name'],
            'action' => $r['action'],
            'target_nrp' => $r['target_nrp'],
            'target_name' => $r['target_name'],
            'summary' => $r['summary'],
            'created_at' => $r['created_at'],
        ];
    }
}
