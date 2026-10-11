<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ControlCenterToken
{
    public function handle(Request $request, Closure $next)
    {
        $expected = (string) config('services.control_center.token');
        abort_if($expected === '', 503, 'Control Center belum dikonfigurasi.');
        abort_unless(hash_equals($expected, (string) $request->bearerToken()), 401, 'Token tidak valid.');

        return $next($request);
    }
}
