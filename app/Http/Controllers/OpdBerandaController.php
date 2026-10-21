<?php

namespace App\Http\Controllers;

use App\Services\BerandaOpdService;
use App\Support\MenuAccess;
use Inertia\Inertia;
use Inertia\Response;

class OpdBerandaController extends Controller
{
    public function __construct(private BerandaOpdService $beranda) {}

    public function index(): Response
    {
        return Inertia::render('OneAcademy/Opd/Beranda', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_academy_menu'),
            ...$this->beranda->build(),
        ]);
    }
}
