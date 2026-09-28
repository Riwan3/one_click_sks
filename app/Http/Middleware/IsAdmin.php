<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        if (! $user->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
