<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBestEmployeeCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canManageBestEmployee();
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'aturan' => ['nullable', 'string'],
            'publish_start' => ['nullable', 'date'],
            'publish_end' => ['nullable', 'date'],
            'latest_start_at' => ['nullable', 'date'],
            'timezone' => ['nullable', 'in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura'],
            'default_question_duration_seconds' => ['required', 'integer', 'min:1'],
            'total_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'attempt_limit' => ['required', 'integer', 'min:1'],
            'shuffle_questions' => ['boolean'],
            'shuffle_options' => ['boolean'],
            'allow_previous_question' => ['boolean'],
            'auto_submit' => ['boolean'],
            'show_score_after_submit' => ['boolean'],
            'show_correct_answer' => ['boolean'],
            'force' => ['nullable', 'boolean'],
            'reason' => ['required_if:force,1', 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul kegiatan wajib diisi.',
            'default_question_duration_seconds.min' => 'Durasi per soal harus lebih dari 0 detik.',
            'attempt_limit.min' => 'Attempt limit minimal 1.',
            'reason.required_if' => 'Alasan override wajib diisi.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $start = $this->input('publish_start');
            $end = $this->input('publish_end');
            $latest = $this->input('latest_start_at');

            if ($start && $end && strtotime($start) >= strtotime($end)) {
                $v->errors()->add('publish_end', 'Waktu publish harus lebih awal dari waktu selesai.');
            }
            if ($latest && $start && strtotime($latest) < strtotime($start)) {
                $v->errors()->add('latest_start_at', 'Batas mulai terakhir tidak boleh sebelum waktu publish.');
            }
            if ($latest && $end && strtotime($latest) > strtotime($end)) {
                $v->errors()->add('latest_start_at', 'Batas mulai terakhir tidak boleh setelah waktu selesai.');
            }
        });
    }
}
