<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ModuleController extends Controller
{
    public function show(string $module): Response
    {
        $found = Arr::first(
            config('modules.list'),
            fn (array $m) => $m['key'] === $module
        );

        if (! $found) {
            throw new NotFoundHttpException;
        }

        return Inertia::render('Modules/Show', [
            'module' => $found,
        ]);
    }
}
