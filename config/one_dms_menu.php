<?php

/**
 * Menu sidebar One-DMS — dummy, href null berarti halamannya belum dibuat.
 */
return [
    ['label' => 'Dashboard', 'icon' => 'LayoutGrid', 'href' => null],
    ['label' => 'DMS', 'icon' => 'FolderOpen', 'href' => '/one-dms'],
    ['label' => 'BA', 'icon' => 'FileText', 'href' => null],
    ['label' => 'Eviden Intercom', 'icon' => 'Mic', 'href' => null],
    [
        'label' => 'Data ICC', 'icon' => 'Database', 'children' => [
            ['label' => 'Deviasi Fatigue', 'icon' => 'Activity', 'href' => null],
            ['label' => 'Smoking Deviasi', 'icon' => 'CircleSlash', 'href' => null],
            ['label' => 'Izin Fatigue', 'icon' => 'UserCheck', 'href' => null],
            ['label' => 'Correct & False Alarm', 'icon' => 'BellRing', 'href' => null],
            ['label' => 'Driver Distraction', 'icon' => 'OctagonAlert', 'href' => null],
            ['label' => 'Phone Detection', 'icon' => 'Phone', 'href' => null],
            ['label' => 'Yawning Detection', 'icon' => 'Frown', 'href' => null],
            ['label' => 'Eye Blink', 'icon' => 'Eye', 'href' => null],
            ['label' => 'Camera Covering', 'icon' => 'VideoOff', 'href' => null],
            ['label' => 'Forward Collision', 'icon' => 'OctagonX', 'href' => null],
            ['label' => 'Video Loss Alarm', 'icon' => 'Video', 'href' => null],
            ['label' => 'No Driver', 'icon' => 'UserX', 'href' => null],
            ['label' => 'Abnormal Speed', 'icon' => 'Gauge', 'href' => null],
        ],
    ],
];
