<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SaveBestEmployeeQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canManageBestEmployee();
    }

    public function rules(): array
    {
        return [
            'question_id' => ['nullable', 'integer'],
            'pertanyaan' => ['required', 'string'],
            'tipe_soal' => ['required', 'in:multiple_choice,short_text,essay'],
            'bobot' => ['required', 'numeric', 'min:0.01'],
            'is_required' => ['boolean'],
            'use_default_duration' => ['boolean'],
            'duration_seconds' => ['nullable', 'integer', 'min:1'],
            'correct_answer' => ['nullable', 'string'],
            'options' => ['array'],
            'options.*.option_text' => ['nullable', 'string'],
            'options.*.is_correct' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'pertanyaan.required' => 'Pertanyaan wajib diisi.',
            'bobot.min' => 'Bobot nilai harus lebih dari 0.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if (! $this->boolean('use_default_duration') && (int) $this->input('duration_seconds', 0) <= 0) {
                $v->errors()->add('duration_seconds', 'Durasi khusus soal harus lebih dari 0 detik (atau centang "gunakan durasi default").');
            }

            if ($this->input('tipe_soal') === 'multiple_choice') {
                $validOptions = collect($this->input('options', []))->filter(fn ($o) => trim($o['option_text'] ?? '') !== '');
                if ($validOptions->count() < 2) {
                    $v->errors()->add('options', 'Multiple choice minimal harus memiliki dua pilihan.');
                } elseif (! $validOptions->contains(fn ($o) => ! empty($o['is_correct']))) {
                    $v->errors()->add('options', 'Multiple choice wajib memiliki satu jawaban benar.');
                }
            }
        });
    }
}
