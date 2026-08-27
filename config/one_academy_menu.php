<?php

/**
 * Menu sidebar One-Academy — dummy, href null berarti halamannya belum dibuat.
 * menu_key wajib unik dalam file ini (dipakai untuk role menu permission).
 */
return [
    ['menu_key' => 'training', 'label' => 'Training', 'icon' => 'Video', 'href' => '/one-academy'],
    ['menu_key' => 'database-pelatihan', 'label' => 'Database Pelatihan', 'icon' => 'Server', 'href' => null],
    ['menu_key' => 'pengajuan-pelatihan', 'label' => 'Pengajuan Pelatihan', 'icon' => 'FilePlus', 'href' => null],
    ['menu_key' => 'monitoring-grade-jabatan', 'label' => 'Monitoring Grade & Jabatan', 'icon' => 'BarChartBig', 'href' => null],
    ['menu_key' => 'evaluasi-kompetensi', 'label' => 'Evaluasi Kompetensi', 'icon' => 'ClipboardCheck', 'href' => null],
    ['menu_key' => 'siswa-ojt', 'label' => 'Siswa OJT', 'icon' => 'GraduationCap', 'href' => null],
    ['menu_key' => 'best-employee', 'label' => 'Best Employee', 'icon' => 'Award', 'href' => null],
];
