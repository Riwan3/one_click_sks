<?php

/**
 * Menu sidebar One-Core & Value — dummy, href null berarti halamannya belum dibuat.
 * menu_key wajib unik dalam file ini (dipakai untuk role menu permission).
 */
return [
    ['menu_key' => 'user', 'label' => 'User', 'icon' => 'ShieldLock', 'href' => '/one-core'],
    ['menu_key' => 'role-akses', 'label' => 'Role & Akses', 'icon' => 'KeySquare', 'href' => '/one-core/roles'],
    ['menu_key' => 'unit', 'label' => 'Unit', 'icon' => 'Truck', 'href' => null],
    ['menu_key' => 'spdk', 'label' => 'SPDK', 'icon' => 'FileHeart', 'href' => null],
    ['menu_key' => 'nomor-penting', 'label' => 'Nomor Penting', 'icon' => 'Phone', 'href' => null],
    ['menu_key' => 'signatory-verify', 'label' => 'Signatory Verify', 'icon' => 'ShieldCheck', 'href' => null],
    ['menu_key' => 'request-reset-password', 'label' => 'Request Reset Password', 'icon' => 'KeyRound', 'href' => null, 'badge' => 3],
];
