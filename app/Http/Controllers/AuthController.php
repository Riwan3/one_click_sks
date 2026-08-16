<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Dummy user data — ganti dengan query ke tabel users begitu backend auth siap.
     */
    private const DUMMY_USERS = [
        ['username' => 'admin', 'password' => 'admin123', 'name' => 'Administrator', 'jabatan' => 'Administrator', 'unit' => 'MASTER'],
        ['username' => '12345', 'password' => 'password123', 'name' => 'Budi Lukman Hakim', 'jabatan' => 'Data Analyst', 'unit' => 'MASTER'],
        ['username' => '26003021', 'password' => 'operator123', 'name' => 'Ahmad Diki Julianto', 'jabatan' => 'Operator', 'unit' => 'GD 825'],
        ['username' => '23002928', 'password' => 'gl123', 'name' => 'Muhammad Saipullah', 'jabatan' => 'Group Leader', 'unit' => 'Disposal'],
    ];

    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request): \Illuminate\Http\RedirectResponse
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = collect(self::DUMMY_USERS)->first(
            fn (array $dummy) => $dummy['username'] === $credentials['username']
                && $dummy['password'] === $credentials['password']
        );

        if (! $user) {
            throw ValidationException::withMessages([
                'username' => 'NRP/Username atau password salah.',
            ]);
        }

        $request->session()->put('dummy_user', $user['name']);
        $request->session()->put('dummy_username', $user['username']);
        $request->session()->put('dummy_jabatan', $user['jabatan']);
        $request->session()->put('dummy_unit', $user['unit']);

        return redirect('/portal');
    }

    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->session()->forget(['dummy_user', 'dummy_username', 'dummy_jabatan', 'dummy_unit']);

        return redirect('/login');
    }
}
