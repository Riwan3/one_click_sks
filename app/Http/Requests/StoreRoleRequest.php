<?php

namespace App\Http\Requests;

use App\Enums\RoleScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'alpha_dash', 'unique:roles,code'],
            'label' => ['required', 'string', 'max:60'],
            'scope' => ['required', new Enum(RoleScope::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode role wajib diisi.',
            'code.alpha_dash' => 'Kode role hanya boleh huruf, angka, strip, dan underscore.',
            'code.unique' => 'Kode role sudah dipakai.',
            'label.required' => 'Nama role wajib diisi.',
            'scope.required' => 'Tipe role wajib dipilih.',
        ];
    }
}
