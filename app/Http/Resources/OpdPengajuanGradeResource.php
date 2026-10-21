<?php

namespace App\Http\Resources;

use App\Models\OpdPengajuanGrade;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpdPengajuanGradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nrp' => $this->nrp,
            'nama' => $this->nama,
            'tahun' => $this->tahun,
            'termin' => $this->termin,
            ...collect([...array_keys(OpdPengajuanGrade::MONITORING_COLUMNS), ...array_keys(OpdPengajuanGrade::EDITABLE_FIELDS)])->mapWithKeys(fn ($f) => [$f => (string) $this->{$f}])->all(),
            'remark' => (string) $this->remark,
            'remark_warna' => $this->remark_warna,
        ];
    }
}
