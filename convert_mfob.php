<?php
// Parser untuk convert MFOB HTML raw ke format table
// Dijalankan sekali, hasilnya di-paste ke config/gmp_rubrics.php

$htmlMFOB = file_get_contents('d:\laragon\www\one_click_laravel\config\gmp_rubrics.php');

// Parse struktur MFOB:
// Columns: PC 1250, PC 2000, PC 4000, 4000 Class
// Rows: Score 5, 4, 3, 2, 1, 0
// Dark cells (empty) di score 4, 2

$converted = [
    'number' => 1,
    'title' => 'Pilih nilai yang sesuai dengan Class Excavator',
    'rubricTitle' => 'Tinggi Jenjang Penggalian',
    'columns' => [
        'PC 1250 Class / CAT 395 / R9100 / EX 1200',
        'PC 2000 Class / EX 2600 / R9200',
        'PC 4000 Class / CAT 6060 / EX 5600',
        '4000 Class',
    ],
    'rows' => [
        ['score' => '5', 'cells' => ['4 m', '4 m', '≥4 m', '8 m'], 'black' => [], 'colspan' => null],
        ['score' => '4', 'cells' => ['', '', '', ''], 'black' => [0, 1, 2, 3], 'colspan' => null],
        ['score' => '3', 'cells' => ['3 - <4 m', '3 - <4 m', '3 - <4 m', '7 - <8 m'], 'black' => [], 'colspan' => null],
        ['score' => '2', 'cells' => ['', '', '', ''], 'black' => [0, 1, 2, 3], 'colspan' => null],
        ['score' => '1', 'cells' => ['2 - <3 m', '2 - <3 m', '2 - <3 m', '6 - <7 m'], 'black' => [], 'colspan' => null],
        ['score' => '0', 'cells' => ['<2 m', '<2 m', '<2 m', '<6 m atau >8 m'], 'black' => [], 'colspan' => null],
    ],
    'options' => [0, 1, 3, 5],
    'format' => 'table',
];

echo json_encode($converted, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
