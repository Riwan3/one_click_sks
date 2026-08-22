<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:20', Rule::unique('users', 'username')->ignore($this->route('user'))],
            'name' => ['required', 'string', 'max:150'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:100'],
            'lokasi' => ['nullable', 'string', 'max:100'],
            'role' => ['required', new Enum(UserRole::class)],
            'password' => ['nullable', 'string', 'min:4'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'NRP/Username wajib diisi.',
            'username.unique' => 'NRP/Username sudah dipakai user lain.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'role.required' => 'Role akses wajib dipilih.',
        ];
    }
}
