<?php

namespace Database\Seeders;

use App\Models\Procedure;
use App\Models\ProcedureAssessment;
use App\Models\ProcedureEvaluation;
use App\Models\ProcedureQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OneProcedureSeeder extends Seeder
{
    public function run(): void
    {
        $procedures = [
            [
                'title' => 'Prosedur Pengisian Bahan Bakar (Refueling) Unit HD & Excavator',
                'category' => 'Operasional Tambang',
                'revision' => 'Rev. 2',
                'description' => 'Petunjuk keselamatan & teknis pengisian bahan bakar solar di area pit & fuel station.',
                'risk_level' => 'high',
                'status' => 'active',
                'effective_date' => '2026-01-15',
                'created_by' => 'Safety Admin',
            ],
            [
                'title' => 'Prosedur Isolasi Energi (Lock Out Tag Out - LOTO)',
                'category' => 'K3',
                'revision' => 'Rev. 4',
                'description' => 'Standar wajib pemutusan & penguncian sumber energi saat perbaikan unit mekanik.',
                'risk_level' => 'critical',
                'status' => 'active',
                'effective_date' => '2026-02-01',
                'created_by' => 'HSE Dept',
            ],
            [
                'title' => 'Prosedur Pemeriksaan Harian Unit (P2H)',
                'category' => 'Perawatan Unit',
                'revision' => 'Rev. 1',
                'description' => 'Ceklis inspeksi komponen kritis unit sebelum mengoperasikan armada tambang.',
                'risk_level' => 'medium',
                'status' => 'active',
                'effective_date' => '2026-01-01',
                'created_by' => 'Plant Admin',
            ],
            [
                'title' => 'Prosedur Penanganan Tumpahan Bahan Kimia & B3',
                'category' => 'Lingkungan',
                'revision' => 'Rev. 0',
                'description' => 'Penanganan darurat tumpahan oli & limbah B3 di area workshop & tambang.',
                'risk_level' => 'high',
                'status' => 'revision_needed',
                'effective_date' => '2025-11-10',
                'created_by' => 'Env Dept',
            ],
        ];

        foreach ($procedures as $pData) {
            $proc = Procedure::create($pData);

            $assessment = ProcedureAssessment::create([
                'procedure_id' => $proc->id,
                'title' => 'Uji Pemahaman SOP - ' . $proc->title,
                'passing_grade' => 80,
                'timer_minutes' => 30,
                'retry_limit' => 3,
                'validity_months' => 6,
                'status' => 'published',
                'created_by' => 'System',
            ]);

            ProcedureQuestion::create([
                'assessment_id' => $assessment->id,
                'question_type' => 'multiple_choice',
                'question' => 'Apa langkah pertama sebelum memulai pekerjaan penanganan sesuai SOP ini?',
                'option_a' => 'Memastikan area aman dan menggunakan APD lengkap',
                'option_b' => 'Langsung menyalakan mesin tanpa ceklis',
                'option_c' => 'Melapor ke pengawas setelah pekerjaan selesai',
                'option_d' => 'Mengosongkan tangki oli terlebih dahulu',
                'correct' => 'A',
                'explanation' => 'SOP keselamatan mengharuskan pemeriksaan kelengkapan APD & keamanan area kerja.',
            ]);

            ProcedureQuestion::create([
                'assessment_id' => $assessment->id,
                'question_type' => 'multiple_choice',
                'question' => 'Berapa jarak aman minimal saat proses berlangsung di area tambang?',
                'option_a' => '2 meter',
                'option_b' => '5 meter',
                'option_c' => '10 meter',
                'option_d' => '15 meter',
                'correct' => 'C',
                'explanation' => 'Jarak aman minimal yang disyaratkan dalam dokumen standar adalah 10 meter.',
            ]);

            // Sample evaluations
            $sampleNrps = ['1001', '1002', '1003'];
            foreach ($sampleNrps as $nrp) {
                $score = rand(75, 95);
                $isPassed = $score >= 80;
                ProcedureEvaluation::create([
                    'assessment_id' => $assessment->id,
                    'nrp' => $nrp,
                    'score' => $score,
                    'status' => $isPassed ? 'passed' : 'failed',
                    'completed_at' => now()->subDays(rand(1, 10)),
                    'certificate_code' => $isPassed ? 'CERT-SOP-' . strtoupper(Str::random(6)) : null,
                    'notes' => $isPassed ? 'Lulus ujian pemahaman SOP' : 'Perlu remedial pemahaman materi',
                ]);
            }
        }
    }
}
