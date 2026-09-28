<?php

/**
 * Menu sidebar One-GMP
 * menu_key wajib unik dalam file ini (dipakai untuk role menu permission).
 */
return [
    ['menu_key' => 'dashboard-gmp', 'label' => 'Dashboard GMP', 'icon' => 'LayoutGrid', 'href' => '/one-gmp/dashboard'],
    ['menu_key' => 'monitoring-gmp', 'label' => 'Monitoring GMP', 'icon' => 'Gauge', 'href' => '/one-gmp/monitoring'],
    ['menu_key' => 'gmp-area', 'label' => 'GMP Area', 'icon' => 'MapPin', 'href' => '/one-gmp/area'],
    ['menu_key' => 'trend-gmp', 'label' => 'Trend GMP', 'icon' => 'TrendingUp', 'href' => '/one-gmp/trend'],
    ['menu_key' => 'temuan-gmp', 'label' => 'Temuan & Tindak Lanjut', 'icon' => 'OctagonAlert', 'href' => '/one-gmp/temuan'],
    [
        'menu_key' => 'penilaian-gmp', 'label' => 'Penilaian', 'icon' => 'ClipboardCheck', 'children' => [
            ['menu_key' => 'penilaian-mhr', 'label' => 'Form MHR', 'icon' => 'FileText', 'href' => '/one-gmp/penilaian/MHR'],
            ['menu_key' => 'penilaian-mfob', 'label' => 'Form MFOB', 'icon' => 'FileText', 'href' => '/one-gmp/penilaian/MFOB'],
            ['menu_key' => 'penilaian-mfco', 'label' => 'Form MFCO', 'icon' => 'FileText', 'href' => '/one-gmp/penilaian/MFCO'],
            ['menu_key' => 'penilaian-disposal', 'label' => 'Form DISPOSAL', 'icon' => 'FileText', 'href' => '/one-gmp/penilaian/DISPOSAL'],
            ['menu_key' => 'penilaian-dewatering', 'label' => 'Form DEWATERING', 'icon' => 'FileText', 'href' => '/one-gmp/penilaian/DEWATERING'],
        ],
    ],
    ['menu_key' => 'approval-gmp', 'label' => 'Approval GMP', 'icon' => 'PenTool', 'href' => '/one-gmp/approval'],
    ['menu_key' => 'master-gmp', 'label' => 'Master GMP', 'icon' => 'Database', 'href' => '/one-gmp/master'],
];
