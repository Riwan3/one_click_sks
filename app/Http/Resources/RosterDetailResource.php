<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Baris tab Daftar Detail: sel jadwal + alokasi unit/section (disisipkan lewat additional 'allocations'). */
class RosterDetailResource extends JsonResource
{
    /** @param  array<int, list<array>>  $allocations  detail_id => alokasi */
    public static function fromGrid(array $details, array $allocations): array
    {
        return array_map(fn (array $d) => (new self($d))->withAllocations($allocations[$d['id']] ?? [])->resolve(), $details);
    }

    private array $allocations = [];

    public function withAllocations(array $allocations): static
    {
        $this->allocations = $allocations;

        return $this;
    }

    public function toArray(Request $request): array
    {
        $d = $this->resource;

        return [
            'id' => (int) $d['id'],
            'nrp' => $d['nrp'],
            'employee_name' => $d['employee_name'],
            'jabatan' => $d['jabatan'],
            'work_date' => $d['work_date'],
            'shift_code' => $d['shift_code'],
            'shift_label' => $d['shift_label'],
            'allocations' => array_map(fn (array $a) => ['unit' => $a['unit'], 'section' => $a['section']], $this->allocations),
        ];
    }
}
