<?php

namespace App\Http\Controllers;

use App\Support\MenuAccess;
use Inertia\Inertia;
use Inertia\Response;

class OneSettingFleetController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('OneSetting/Fleet/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_setting_menu'),
        ]);
    }
}
