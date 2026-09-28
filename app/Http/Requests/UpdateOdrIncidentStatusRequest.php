<?php

namespace App\Http\Requests;

use App\Enums\OdrIncidentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOdrIncidentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('access-compliance-report');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OdrIncidentStatus::class)],
            'target_selesai' => ['nullable', 'date'],
        ];
    }
}
