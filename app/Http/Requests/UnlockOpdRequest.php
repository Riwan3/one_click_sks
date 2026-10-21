<?php

namespace App\Http\Requests;

use App\Support\AuthorizationPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UnlockOpdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['password' => ['required', 'string']];
    }

    public function messages(): array
    {
        return ['password.required' => 'Password otorisasi wajib diisi.'];
    }

    public function after(): array
    {
        return [
            fn (Validator $validator) => $validator->errors()->isNotEmpty()
                || AuthorizationPassword::matches($this->input('password'))
                || $validator->errors()->add('password', 'Password otorisasi salah.'),
        ];
    }
}
