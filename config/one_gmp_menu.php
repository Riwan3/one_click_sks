<?php

/**
 * Menu sidebar One-GMP
 * menu_key wajib unik dalam file ini (dipakai untuk role menu permission).
 */
return [
    ['menu_key' => 'dashboard-gmp', 'label' => 'Dashboard GMP', 'icon' => 'LayoutGrid', 'href' => '/one-gmp/dashboard'],
    ['menu_key' => 'trend-gmp', 'label' => 'Trend GMP', 'icon' => 'TrendingUp', 'href' => '/one-gmp/trend'],
    [
        'menu_key' => 'gmp-area', 'label' => 'GMP Area', 'icon' => 'MapPin', 'children' => [
            ['menu_key' => 'area-mhr', 'label' => 'MHR', 'icon' => 'Route', 'href' => '/one-gmp/area/MHR'],
            ['menu_key' => 'area-mfob', 'label' => 'MFOB', 'icon' => 'Layers', 'href' => '/one-gmp/area/MFOB'],
            ['menu_key' => 'area-mfoc', 'label' => 'MFOC', 'icon' => 'Flame', 'href' => '/one-gmp/area/MFOC'],
            ['menu_key' => 'area-disposal', 'label' => 'DISPOSAL', 'icon' => 'Mountain', 'href' => '/one-gmp/area/DISPOSAL'],
            ['menu_key' => 'area-dewatering', 'label' => 'DEWATERING', 'icon' => 'Droplets', 'href' => '/one-gmp/area/DEWATERING'],
        ],
    ],
    [
        'menu_key' => 'penilaian-gmp', 'label' => 'Penilaian GMP', 'icon' => 'ClipboardCheck', 'children' => [
            ['menu_key' => 'penilaian-mhr', 'label' => 'Form MHR', 'icon' => 'FileText', 'href' => '/one-gmp/penilaian/MHR'],
            ['menu_key' => 'penilaian-mfob', 'label' => 'Form MFOB', 'icon' => 'FileText', 'href' => '/one-gmp/penilaian/MFOB'],
            ['menu_key' => 'penilaian-mfoc', 'label' => 'Form MFOC', 'icon' => 'FileText', 'href' => '/one-gmp/penilaian/MFOC'],
            ['menu_key' => 'penilaian-disposal', 'label' => 'Form DISPOSAL', 'icon' => 'FileText', 'href' => '/one-gmp/penilaian/DISPOSAL'],
            ['menu_key' => 'penilaian-dewatering', 'label' => 'Form DEWATERING', 'icon' => 'FileText', 'href' => '/one-gmp/penilaian/DEWATERING'],
        ],
    ],
    ['menu_key' => 'approval-gmp', 'label' => 'Approval GMP', 'icon' => 'CheckSquare', 'href' => '/one-gmp/approval'],
    ['menu_key' => 'master-gmp', 'label' => 'Master GMP', 'icon' => 'Database', 'href' => '/one-gmp/master'],
];
