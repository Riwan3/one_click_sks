<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMppMasterCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user() && in_array(auth()->user()->role, ['admin', 'pimpinan']);
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255', 'unique:mpp_master_categories,nama' . ($this->route('mpp_master_category') ? ',' . $this->route('mpp_master_category')->id : '')],
            'kode' => ['required', 'string', 'max:100', 'unique:mpp_master_categories,kode' . ($this->route('mpp_master_category') ? ',' . $this->route('mpp_master_category')->id : '')],
            'plan_mp' => ['required', 'integer', 'min:0', 'max:100'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.unique' => 'Nama kategori sudah ada.',
            'kode.required' => 'Kode kategori wajib diisi.',
            'kode.unique' => 'Kode kategori sudah ada.',
            'plan_mp.required' => 'Plan MP wajib diisi.',
            'plan_mp.integer' => 'Plan MP harus berupa angka.',
            'plan_mp.max' => 'Plan MP maksimal 100.',
        ];
    }
}
