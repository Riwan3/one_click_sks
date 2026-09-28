<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIzinFatigueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer'],
            'tanggal' => ['required', 'date'],
            'shift' => ['required', 'string'],
            'nrp' => ['nullable', 'string'],
            'nama_operator' => ['nullable', 'string'],
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'jam_akhir' => ['nullable', 'date_format:H:i'],
            'total_jam' => ['nullable', 'string'],
        ];
    }
}
