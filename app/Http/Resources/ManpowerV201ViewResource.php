<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Model Setting Harian (hasil ManpowerV201SettingService::view / viewWithCodes) untuk halaman Inertia. */
class ManpowerV201ViewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $view = $this->resource;

        return [
            'setting' => $view['setting'] ? $this->setting($view['setting']) : null,
            'hadir' => (int) $view['hadir'],
            'summary' => $view['summary'],
            'warnings' => $view['warnings'],
            'tree' => collect($view['tree'])->map(fn ($csa) => $this->csa($csa))->values(),
            'sisa' => collect($view['sisa'])->map(fn ($p) => [
                'nrp' => (string) $p['nrp'], 'name' => $p['name'], 'unit' => $p['unit'],
                'kind' => $p['kind'], 'home_label' => $p['home_label'], 'released' => (bool) $p['released'],
            ])->values(),
            'gl' => collect($view['gl'])->map(fn ($p) => [
                'nrp' => (string) $p['nrp'], 'name' => $p['name'], 'unit' => $p['unit'], 'phone' => $p['phone'],
            ])->values(),
            'lain' => collect($view['lain'])->map(fn ($o) => [
                'nrp' => (string) $o['nrp'], 'name' => $o['name'], 'phone' => $o['phone'],
                'label' => $o['label'], 'source' => $o['source'], 'absent' => (bool) $o['absent'],
            ])->values(),
            'codes' => isset($view['codes']) ? $this->codes($view['codes']) : null,
        ];
    }

    private function setting(array $s): array
    {
        return [
            'id' => (int) $s['id'], 'work_date' => $s['work_date'], 'shift' => $s['shift'], 'status' => $s['status'],
            'synced_at' => $s['synced_at'], 'updated_by' => $s['updated_by'], 'updated_at' => $s['updated_at'],
        ];
    }

    private function csa(array $csa): array
    {
        return [
            'id' => (int) $csa['id'], 'name' => $csa['name'],
            'categories' => collect($csa['categories'])->map(fn ($cat) => [
                'id' => (int) $cat['id'], 'name' => $cat['name'], 'col_pos' => (int) $cat['col_pos'],
                'units' => collect($cat['units'])->map(fn ($u) => $this->unit($u))->values(),
            ])->values(),
        ];
    }

    private function unit(array $u): array
    {
        $a = $u['assignment'];

        return [
            'id' => (int) $u['id'], 'unit' => $u['unit'],
            'is_spare_slot' => (bool) $u['is_spare_slot'], 'is_high_risk' => (bool) $u['is_high_risk'],
            'assignment' => $a ? [
                'nrp' => (string) $a['nrp'], 'employee_name' => $a['employee_name'], 'phone' => $a['phone'],
                'source' => $a['source'], 'absent' => (bool) $a['absent'], 'spare' => (bool) $a['spare'],
            ] : null,
            'double' => collect($u['double'])->map(fn ($c) => ['nrp' => (string) $c['nrp'], 'name' => $c['name']])->values(),
        ];
    }

    /** Kunci NRP numerik dijadikan objek JSON ({nrp: kode}), bukan list. */
    private function codes(array $codes): array
    {
        return ['roster' => (object) $codes['roster'], 'ss6' => (object) $codes['ss6']];
    }
}
