<?php

namespace App\Http\Requests;

use App\Services\MoncuCalendarService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Detail READ-ONLY satu tanggal MONCU Calendar (parameter sama dengan api/roster-v2/moncu-calendar.php V1). */
class MoncuCalendarDayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canViewRosterV2();
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'group' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:150'],
            'search' => ['nullable', 'string', 'max:50'],
            'section' => ['nullable', Rule::in(MoncuCalendarService::SECTIONS)],
            'page' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return ['date.*' => 'Parameter date harus YYYY-MM-DD dan tanggal yang nyata.'];
    }
}
