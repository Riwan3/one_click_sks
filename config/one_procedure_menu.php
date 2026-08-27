<?php

/**
 * Menu sidebar One-Procedure — dummy, href null berarti halamannya belum dibuat.
 * menu_key wajib unik dalam file ini (dipakai untuk role menu permission).
 */
return [
    ['menu_key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'FileText', 'href' => '/one-procedure'],
    ['menu_key' => 'prosedur-operasional', 'label' => 'Prosedur Operasional', 'icon' => 'ClipboardCheck', 'href' => null],
    ['menu_key' => 'activity', 'label' => 'Activity', 'icon' => 'Activity', 'href' => null],
    ['menu_key' => 'evaluasi-prosedur', 'label' => 'Evaluasi Prosedur', 'icon' => 'FileBarChart2', 'href' => null],
];
