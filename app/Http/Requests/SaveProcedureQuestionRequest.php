<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveProcedureQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-procedure-evaluation');
    }

    public function rules(): array
    {
        $isTrueFalse = $this->input('question_type') === 'true_false';

        return [
            'question_type' => ['required', 'in:multiple_choice,true_false,case_based'],
            'question' => ['required', 'string'],
            'option_a' => [$isTrueFalse ? 'nullable' : 'required', 'string', 'max:255'],
            'option_b' => [$isTrueFalse ? 'nullable' : 'required', 'string', 'max:255'],
            'option_c' => ['nullable', 'string', 'max:255'],
            'option_d' => ['nullable', 'string', 'max:255'],
            'correct' => ['required', $isTrueFalse ? 'in:A,B' : 'in:A,B,C,D'],
            'explanation' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'Pertanyaan/pernyataan wajib diisi',
            'correct.required' => 'Pilih jawaban benar terlebih dahulu',
            'option_a.required' => 'Opsi A wajib diisi',
            'option_b.required' => 'Opsi B wajib diisi',
        ];
    }
}
