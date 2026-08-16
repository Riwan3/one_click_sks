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
        ['username' => 'admin', 'password' => 'admin123', 'name' => 'Administrator'],
        ['username' => '12345', 'password' => 'password123', 'name' => 'Budi Lukman Hakim'],
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

        return redirect('/portal');
    }

    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->session()->forget(['dummy_user', 'dummy_username']);

        return redirect('/login');
    }
}
