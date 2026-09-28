<?php
// config/gmp_scoring.php
//
// Sumber tunggal pemetaan skor GMP agar perhitungan di controller (OneGmpController)
// dan di dashboard selalu konsisten. Nilai persis disalin dari V1 (config/gmp_scoring.php)
// supaya hasil skor sama antara V1 dan port Laravel ini.
//
// - percent_map : skor mentah (0-5) -> persentase performa (0-100%)
// - weight_map  : bobot tiap nomor pertanyaan per tipe GMP (jumlah bobot per tipe = 100)
//
// Metodologi skor area (resmi, sesuai "Rekap Skor Terendah per Lokasi"):
//   skor_area = SUM atas pertanyaan( percent_map[ MIN(score_raw area-itu) ] * weight / 100 )
//   skor_keseluruhan = rata-rata skor_area antar lokasi.

return [
    'percent_map' => [0 => 0, 1 => 50, 2 => 60, 3 => 70, 4 => 85, 5 => 100],

    'weight_map' => [
        'DEWATERING' => [
            1 => 6, 2 => 10, 3 => 10, 4 => 8, 5 => 8,
            6 => 12, 7 => 10, 8 => 2, 9 => 4, 10 => 6,
            11 => 6, 12 => 2, 13 => 2, 14 => 4, 15 => 10,
        ],
        'DISPOSAL' => [
            1 => 6, 2 => 10, 3 => 12, 4 => 4, 5 => 4,
            6 => 10, 7 => 4, 8 => 6, 9 => 2, 10 => 8,
            11 => 12, 12 => 10, 13 => 8, 14 => 4,
        ],
        'MHR' => [
            1 => 20, 2 => 4, 3 => 14, 4 => 10, 5 => 10,
            6 => 8, 7 => 6, 8 => 6, 9 => 4, 10 => 10,
            11 => 4, 12 => 4,
        ],
        'MFOB' => [
            1 => 14, 2 => 12, 3 => 4, 4 => 10, 5 => 4,
            6 => 12, 7 => 14, 8 => 10, 9 => 10, 10 => 8,
            11 => 2,
        ],
        'MFCO' => [
            1 => 10, 2 => 2, 3 => 8, 4 => 10, 5 => 4,
            6 => 8, 7 => 4, 8 => 2, 9 => 4, 10 => 10,
            11 => 12, 12 => 8, 13 => 8, 14 => 2, 15 => 8,
        ],
    ],
];
