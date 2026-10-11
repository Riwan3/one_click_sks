<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ControlCenterReport;

/**
 * Endpoint baca-saja untuk OneClick Control Center (mode pull; lihat
 * middleware cc.token). Isinya dibangun ControlCenterReport, yang juga
 * dipakai command cc:push (mode push).
 */
class ControlCenterController extends Controller
{
    public function status(ControlCenterReport $report)
    {
        return response()->json(['data' => $report->status()]);
    }

    public function activity(ControlCenterReport $report)
    {
        return response()->json(['data' => $report->activity()]);
    }

    public function menus(ControlCenterReport $report)
    {
        return response()->json(['data' => $report->menus()]);
    }
}
