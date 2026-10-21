<?php

/**
 * Menu sidebar One-Academy
 * menu_key wajib unik dalam file ini (dipakai untuk role menu permission).
 */
return [
    ['menu_key' => 'training', 'label' => 'Training', 'icon' => 'Video', 'href' => '/one-academy'],
    [
        'menu_key' => 'opd', 'label' => 'OPD', 'icon' => 'Network', 'children' => [
            ['menu_key' => 'monitoring-grade-jabatan', 'label' => 'Monitoring Grade & Jabatan', 'icon' => 'BarChartBig', 'href' => '/one-academy/monitoring-grade'],
            ['menu_key' => 'opd-beranda', 'label' => 'Beranda OPD', 'icon' => 'Gauge', 'href' => '/one-academy/opd/beranda'],
            ['menu_key' => 'opd-pengajuan-grade', 'label' => 'Pengajuan Grade', 'icon' => 'CircleArrowUp', 'href' => '/one-academy/opd/pengajuan-grade'],
            [
                'menu_key' => 'opd-master-grade', 'label' => 'Master Grade', 'icon' => 'Layers', 'children' => [
                    ['menu_key' => 'opd-minimal-grade', 'label' => 'Minimal Grade', 'icon' => 'Target', 'href' => '/one-academy/opd/master-grade/minimal-grade'],
                ],
            ],
        ],
    ],
    ['menu_key' => 'evaluasi-kompetensi', 'label' => 'Evaluasi Kompetensi', 'icon' => 'ClipboardCheck', 'href' => '/one-academy/evaluasi-kompetensi'],
    ['menu_key' => 'best-employee', 'label' => 'Best Employee', 'icon' => 'Award', 'href' => '/one-academy/best-employee'],
];
