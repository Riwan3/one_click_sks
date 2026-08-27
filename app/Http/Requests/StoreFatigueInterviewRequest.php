<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFatigueInterviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'pengawas_id' => ['required', 'integer', 'exists:employees,id'],
            'nomor_unit' => ['nullable', 'string', 'max:50'],
            'area' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'jam' => ['nullable', 'date_format:H:i'],
            'q1_shift_day' => ['required', 'string', 'max:255'],
            'q2_body_condition' => ['nullable', 'string', 'max:255'],
            'q3_reason' => ['required', 'string'],
            'q3_background' => ['required', 'string'],
            'q4_rest_hours' => ['required', 'string', 'max:50'],
            'q5_rest_quality' => ['required', 'string', 'max:255'],
            'q5_bad_quality_reason' => ['nullable', 'string'],
            'q6_home_comfort' => ['required', 'string', 'max:255'],
            'q6_discomfort_reason' => ['nullable', 'string'],
            'q7_other_activities' => ['required', 'string', 'max:255'],
            'q7_activity_details' => ['nullable', 'string'],
            'q8_education' => ['required', 'string'],
            'q9_heavy_fatigue_day' => ['nullable', 'string', 'max:100'],
            'q10_fit_plan' => ['nullable', 'string'],
            'q11_suggestions' => ['required', 'string'],
            'ttd_pengawas' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'employee_id.exists' => 'Karyawan tidak ditemukan.',
            'pengawas_id.required' => 'Pengawas wajib dipilih.',
            'pengawas_id.exists' => 'Pengawas tidak ditemukan.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'q1_shift_day.required' => 'Q1: Shift wajib diisi.',
            'q3_reason.required' => 'Q3 Alasan wajib diisi.',
            'q3_background.required' => 'Q3 Latar Belakang wajib diisi.',
            'q4_rest_hours.required' => 'Q4: Jam Istirahat wajib diisi.',
            'q5_rest_quality.required' => 'Q5: Kualitas Istirahat wajib diisi.',
            'q6_home_comfort.required' => 'Q6: Kenyamanan Rumah wajib diisi.',
            'q7_other_activities.required' => 'Q7: Aktivitas Lain wajib diisi.',
            'q8_education.required' => 'Q8: Gangguan Tidur wajib diisi.',
            'q11_suggestions.required' => 'Q11: Saran/Masukan wajib diisi.',
        ];
    }
}
