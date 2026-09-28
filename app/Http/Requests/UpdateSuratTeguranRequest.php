<?php

namespace App\Http\Requests;

class UpdateSuratTeguranRequest extends StoreSuratTeguranRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['foto'] = ['nullable'];
        if ($this->hasFile('foto')) {
            $rules['foto'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'];
        }
        $rules['hapus_foto'] = ['nullable', 'boolean'];

        return $rules;
    }
}
