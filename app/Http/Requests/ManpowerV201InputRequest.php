<?php

namespace App\Http\Requests;

/** Aksi tulis Setting Manpower v2.01. Aturan bisnis & pesan error tetap di Service (sama seperti V1). */
class ManpowerV201InputRequest extends ManpowerV201Request
{
    public function rules(): array
    {
        return [
            'date' => ['nullable', 'string'],
            'shift' => ['nullable', 'string'],
            'nrp' => ['nullable', 'string'],
            'label' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'units' => ['nullable', 'string'],
            'unit' => ['nullable', 'string'],
            'dir' => ['nullable', 'string'],
        ];
    }
}
