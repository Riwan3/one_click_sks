<?php

namespace App\Http\Middleware;

use App\Support\OpdLock;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOpdUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if (OpdLock::isUnlocked($request)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'One-OPD terkunci. Muat ulang halaman lalu masukkan password.'], 403);
        }

        return redirect()->route('one-academy.opd.pengajuan.index')->with('error', 'One-OPD terkunci. Masukkan password otorisasi.');
    }
}
