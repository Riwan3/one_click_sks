<?php

namespace App\Http\Requests;

/** Aksi tulis Setting Fleet harian. Akses sama seperti Setting Manpower; aturan bisnis & pesan error di Service. */
class FleetInputRequest extends ManpowerV201Request
{
    public function rules(): array
    {
        return [
            'date' => ['nullable', 'string'],
            'shift' => ['nullable', 'string'],
            'loader_code' => ['nullable', 'string', 'max:50'],
            'hauler_code' => ['nullable', 'string', 'max:50'],
            'group_name' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
            'front' => ['nullable', 'string', 'max:100'],
            'refueling_time' => ['nullable', 'string', 'max:50'],
            'distance' => ['nullable', 'integer', 'min:0'],
            'from_date' => ['nullable', 'string'],
            'from_shift' => ['nullable', 'string'],
            'replace' => ['nullable', 'boolean'],
        ];
    }

    /** Field loader yang diubah di setting harian (hanya yang dikirim). */
    public function loaderFields(): array
    {
        return $this->only(['group_name', 'area', 'front', 'refueling_time', 'distance']);
    }
}
