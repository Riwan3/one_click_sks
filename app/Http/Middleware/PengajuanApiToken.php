<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Token bersama untuk OneClick HO (Authorization: Bearer <token> atau X-API-Key). Tanpa token di .env, API menolak semua permintaan. */
class PengajuanApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.pengajuan_api.token');
        if ($expected === '') {
            return response()->json(['success' => false, 'message' => 'API belum diaktifkan di site ini (PENGAJUAN_API_TOKEN belum diatur).'], 503);
        }

        $given = (string) ($request->bearerToken() ?: $request->header('X-API-Key'));
        if (! hash_equals($expected, $given)) {
            usleep(300000); // perlambat tebakan token

            return response()->json(['success' => false, 'message' => 'Token tidak valid.'], 401);
        }

        return $next($request);
    }
}
