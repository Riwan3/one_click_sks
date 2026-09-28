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
            // 1. Change Shift pada CSA Support
            [
                'title' => 'SOP Serah Terima Shift & Ceklis Unit CSA Support',
                'category' => 'Change Shift pada CSA Support',
                'revision' => 'Rev. 2',
                'description' => 'Tata cara penyerahan unit kerja, laporan kondisi medan jalan hauling, pemeriksaan fuel level & pengisian log sheet shift antar operator.',
                'risk_level' => 'medium',
                'status' => 'active',
                'effective_date' => '2026-01-15',
                'created_by' => 'Ops CSA Team',
            ],
            [
                'title' => 'Prosedur Parkir Aman & Pemasangan Wheel Choke CSA',
                'category' => 'Change Shift pada CSA Support',
                'revision' => 'Rev. 1',
                'description' => 'Standar manuver parkir aman, penerapan rem parkir, dan pemasangan wheel choke di rest area CSA support saat pergantian giliran kerja.',
                'risk_level' => 'medium',
                'status' => 'active',
                'effective_date' => '2026-02-01',
                'created_by' => 'Ops CSA Team',
            ],

            // 2. Dewatering
            [
                'title' => 'Prosedur Keselamatan Kerja di Area Dewatering & Sump Pit',
                'category' => 'Dewatering',
                'revision' => 'Rev. 3',
                'description' => 'Standar jarak aman pengoperasian unit di dekat bibir galian sump, tanggul pengaman safety berm, dan pemantauan instalasi pompa lumpur.',
                'risk_level' => 'high',
                'status' => 'active',
                'effective_date' => '2026-01-10',
                'created_by' => 'Dewatering Dept',
            ],
            [
                'title' => 'Instruksi Tanggap Darurat Kenaikan Muka Air & Longsoran Sump Pit',
                'category' => 'Dewatering',
                'revision' => 'Rev. 1',
                'description' => 'Prosedur evakuasi unit dan personel saat curah hujan tinggi serta indikasi peningkatan debit air pit melebihi ambang batas aman.',
                'risk_level' => 'critical',
                'status' => 'active',
                'effective_date' => '2026-02-15',
                'created_by' => 'HSE Dept',
            ],

            // 3. Peraturan Perusahaan PT. Putra Perkasa Abadi
            [
                'title' => 'Kebijakan Mutu, Keselamatan Pertambangan & Lingkungan Hidup (KPLH)',
                'category' => 'Peraturan Perusahaan PT. Putra Perkasa Abadi',
                'revision' => 'Rev. 5',
                'description' => 'Komitmen PT Putra Perkasa Abadi dalam penerapan keselamatan kerja zero accident, perlindungan lingkungan, dan standar mutu operasional.',
                'risk_level' => 'low',
                'status' => 'active',
                'effective_date' => '2026-01-01',
                'created_by' => 'SHE Dept',
            ],
            [
                'title' => 'Tata Tertib Kerja, Disiplin & Kode Etik Karyawan PT PPA',
                'category' => 'Peraturan Perusahaan PT. Putra Perkasa Abadi',
                'revision' => 'Rev. 2',
                'description' => 'Pedoman kedisiplinan operasional, larangan penggunaan HP saat mengemudi, kepatuhan APD lengkap, dan integritas kerja karyawan.',
                'risk_level' => 'medium',
                'status' => 'active',
                'effective_date' => '2026-01-01',
                'created_by' => 'HR Dept',
            ],
            [
                'title' => 'Ketentuan Waktu Istirahat, Fatigue Management & Batas Jam Lembur',
                'category' => 'Peraturan Perusahaan PT. Putra Perkasa Abadi',
                'revision' => 'Rev. 3',
                'description' => 'Standar kepatuhan batas jam mengemudi (maksimal 12 jam shift), waktu istirahat teratur, dan pelaporan wajib saat unfit/fatigue.',
                'risk_level' => 'high',
                'status' => 'active',
                'effective_date' => '2026-02-01',
                'created_by' => 'SHE Dept',
            ],

            // 4. IBPR / HIRA
            [
                'title' => 'IBPR Pengoperasian Dump Truck HD 785 di Front Loading & Dumping',
                'category' => 'IBPR / HIRA',
                'revision' => 'Rev. 4',
                'description' => 'Identifikasi Bahaya & Penilaian Risiko aktivitas loading batubara/OB, blind spot alat muat, dan jarak antri aman di loading point.',
                'risk_level' => 'critical',
                'status' => 'active',
                'effective_date' => '2026-01-15',
                'created_by' => 'Safety Admin',
            ],
            [
                'title' => 'HIRA Manuver Dumping di Disposal & Area Timbunan Overburden',
                'category' => 'IBPR / HIRA',
                'revision' => 'Rev. 3',
                'description' => 'Penilaian risiko amblas, bahaya longsor tebing disposal, kepatuhan arahan spotter/group leader, dan ketinggian safety bunding minimal 3/4 roda.',
                'risk_level' => 'high',
                'status' => 'active',
                'effective_date' => '2026-01-20',
                'created_by' => 'Safety Admin',
            ],
            [
                'title' => 'IBPR Interaksi Alat Berat & Unit Light Vehicle di Jalur Hauling',
                'category' => 'IBPR / HIRA',
                'revision' => 'Rev. 2',
                'description' => 'Penilaian bahaya tabrakan di persimpangan jalan tambang, batas kecepatan maksimal 40 km/jam, dan disiplin radio komunikasi dua arah.',
                'risk_level' => 'critical',
                'status' => 'active',
                'effective_date' => '2026-02-05',
                'created_by' => 'Safety Admin',
            ],

            // 5. P2H & Perawatan Unit
            [
                'title' => 'Prosedur Pemeriksaan Harian Unit (P2H) Armada Tambang',
                'category' => 'P2H & Perawatan Unit',
                'revision' => 'Rev. 3',
                'description' => 'Ceklis inspeksi komponen kritis unit (kemudi, rem, tyre, level oli, APAR, dan fungsi safety radio) sebelum mengoperasikan armada.',
                'risk_level' => 'high',
                'status' => 'active',
                'effective_date' => '2026-01-01',
                'created_by' => 'Plant Dept',
            ],
            [
                'title' => 'Prosedur Pengisian Bahan Bakar (Refueling) Unit HD & Excavator',
                'category' => 'P2H & Perawatan Unit',
                'revision' => 'Rev. 2',
                'description' => 'Petunjuk teknis dan keselamatan pengisian solar di fuel station/fuel truck pit, mematikan mesin & prosedur isolasi kunci kontak.',
                'risk_level' => 'high',
                'status' => 'active',
                'effective_date' => '2026-01-15',
                'created_by' => 'Fuel Dept',
            ],
            [
                'title' => 'Prosedur Isolasi Energi (Lock Out Tag Out - LOTO)',
                'category' => 'P2H & Perawatan Unit',
                'revision' => 'Rev. 4',
                'description' => 'Standar wajib pemutusan & penguncian sumber energi saat perbaikan, servis berkala, atau unit breakdown di lapangan.',
                'risk_level' => 'critical',
                'status' => 'active',
                'effective_date' => '2026-02-01',
                'created_by' => 'Plant Dept',
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
