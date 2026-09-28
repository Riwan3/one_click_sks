<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GmpPenilaianSeeder extends Seeder
{
    private const QUESTIONS = [
        'MHR' => [
            1 => ['section_key' => 'teknis', 'name' => 'Undulasi & Lubang', 'allowed_scores' => [0, 1, 2, 5]],
            2 => ['section_key' => 'teknis', 'name' => 'Grade Jalan', 'allowed_scores' => [0, 1, 2, 5]],
            3 => ['section_key' => 'teknis', 'name' => 'Lebar Jalan', 'allowed_scores' => [0, 1, 5]],
            4 => ['section_key' => 'teknis', 'name' => 'Superelevasi & Crossfall', 'allowed_scores' => [0, 1, 2, 5]],
            5 => ['section_key' => 'keselamatan', 'name' => 'Bundwall', 'allowed_scores' => [0, 1, 2, 5]],
            6 => ['section_key' => 'keselamatan', 'name' => 'Drainase', 'allowed_scores' => [0, 1, 2, 5]],
            7 => ['section_key' => 'keselamatan', 'name' => 'Culvert', 'allowed_scores' => [0, 1, 2, 5]],
            8 => ['section_key' => 'keselamatan', 'name' => 'Divider & Median', 'allowed_scores' => [0, 1, 5]],
            9 => ['section_key' => 'keselamatan', 'name' => 'Sudut Simpang', 'allowed_scores' => [0, 1, 5]],
            10 => ['section_key' => 'keselamatan', 'name' => 'Material Spoil', 'allowed_scores' => [0, 1, 2, 5]],
            11 => ['section_key' => 'keselamatan', 'name' => 'Rambu & Patok', 'allowed_scores' => [0, 1, 5]],
            12 => ['section_key' => 'keselamatan', 'name' => 'Debu & Blindspot', 'allowed_scores' => [0, 1, 5]],
        ],
    ];

    public function run(): void
    {
        $type = 'MHR';
        $questions = self::QUESTIONS[$type];
        $locations = ['KM 5 - Hauling A', 'KM 12 - Hauling B', 'Simpang Pit Utara', 'Akses Disposal C', 'Jalan Tambang Selatan'];
        $penilaiList = ['Agus Setiawan', 'Budi Harjo', 'Chandra Kirana'];

        $weeksAgo = [0, 1];

        foreach ($weeksAgo as $offset) {
            $tanggal = now()->subWeeks($offset)->startOfWeek();
            $week = 'Week ' . $tanggal->format('W') . ' - ' . $tanggal->format('Y');

            foreach ($locations as $idx => $lokasi) {
                $formId = DB::table('gmp_penilaian_form')->insertGetId([
                    'tipe_gmp' => $type,
                    'week' => $week,
                    'lokasi' => $lokasi,
                    'penilai' => $penilaiList[$idx % count($penilaiList)],
                    'nrp' => '2' . str_pad((string) (100 + $idx), 5, '0', STR_PAD_LEFT),
                    'tanggal_gmp' => $tanggal->copy()->addDays($idx % 5)->format('Y-m-d'),
                    'foto_area' => null,
                    'total_score' => 0,
                    'total_percent' => 0,
                    'question_count' => count($questions),
                    'keterangan' => 'Data seed untuk pengujian dashboard GMP.',
                    'created_by' => 'seeder',
                    'status' => 'approved',
                    'approved_by' => 'System',
                    'approved_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalRaw = 0;
                $totalWeighted = 0;
                $weightMap = config('gmp_scoring.weight_map')[$type] ?? [];
                $percentMap = config('gmp_scoring.percent_map');

                foreach ($questions as $qNo => $meta) {
                    $scores = $meta['allowed_scores'];
                    // Beberapa lokasi sengaja diberi skor rendah agar Pareto/rekap punya variasi nyata.
                    $weakSpot = in_array($qNo, [3, 7, 9], true) && $idx % 2 === 0;
                    $raw = $weakSpot ? $scores[array_rand(array_slice($scores, 0, 2))] : $scores[array_rand($scores)];

                    $pct = $percentMap[$raw] ?? 0;
                    $weight = $weightMap[$qNo] ?? 0;
                    $totalRaw += $raw;
                    $totalWeighted += $pct * $weight / 100;

                    DB::table('gmp_penilaian_form_detail')->insert([
                        'form_id' => $formId,
                        'question_no' => $qNo,
                        'section_key' => $meta['section_key'],
                        'criterion_title' => $meta['name'],
                        'criterion_name' => $meta['name'],
                        'score_raw' => $raw,
                        'score_percent' => $pct,
                        'foto_detail' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('gmp_penilaian_form')->where('id', $formId)->update([
                    'total_score' => $totalRaw,
                    'total_percent' => round($totalWeighted, 1),
                ]);
            }
        }
    }
}
