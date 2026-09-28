<?php

namespace App\Enums;

/** Jenis tindakan perbaikan pasca-insiden. */
enum OdrIncidentActionType: string
{
    case Pencegahan = 'pencegahan';
    case Mitigasi = 'mitigasi';
}
