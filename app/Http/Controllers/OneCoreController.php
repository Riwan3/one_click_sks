<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OneCoreController extends Controller
{
    private const DUMMY_PASSWORD = 'admin123';

    private const PER_PAGE = 10;

    /**
     * Dummy data — ganti dengan query database begitu backend One-Core siap.
     */
    public function dashboard(Request $request): Response
    {
        if (! $request->session()->get('one_core_unlocked')) {
            return Inertia::render('OneCore/Lock', [
                'error' => $request->session()->get('one_core_error'),
            ]);
        }

        $request->session()->forget('one_core_error');

        $search = trim((string) $request->string('search'));
        $page = max(1, (int) $request->integer('page', 1));

        $users = collect($this->dummyUsers())
            ->when($search !== '', fn ($rows) => $rows->filter(
                fn (array $row) => Str::contains(strtolower($row['username']), strtolower($search))
                    || Str::contains(strtolower($row['name']), strtolower($search))
            ))
            ->values();

        $total = $users->count();
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min($page, $lastPage);

        return Inertia::render('OneCore/Dashboard', [
            'user' => [
                'displayName' => session('dummy_user', 'Guest User'),
                'username' => session('dummy_username', '00000'),
                'jabatan' => 'Data Analyst',
                'unit' => 'MASTER',
                'foto' => null,
            ],
            'notifCounts' => [
                'signature' => 2,
                'notification' => 3,
                'message' => 1,
            ],
            'menu' => config('one_core_menu'),
            'search' => $search,
            'users' => $users->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values()->all(),
            'pagination' => [
                'currentPage' => $page,
                'lastPage' => $lastPage,
                'total' => $total,
            ],
        ]);
    }

    public function unlock(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['password' => 'required|string']);

        if ($request->input('password') !== self::DUMMY_PASSWORD) {
            $request->session()->put('one_core_error', 'Password otorisasi salah.');

            return redirect('/one-core');
        }

        $request->session()->put('one_core_unlocked', true);

        return redirect('/one-core');
    }

    public function lock(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->session()->forget('one_core_unlocked');

        return redirect('/one-core');
    }

    private function dummyUsers(): array
    {
        $names = ['Ahmad Fauzi', 'Budi Santoso', 'Candra Wijaya', 'Dedi Kurniawan', 'Eko Prasetyo', 'Fajar Nugroho', 'Gilang Ramadhan', 'Hendra Saputra', 'Irfan Hakim', 'Joko Susilo', 'Wahyu Ananda', 'Yusuf Maulana', 'Zaki Ramadhan', 'Putri Amelia', 'Sari Wulandari'];
        $jabatans = ['Operator', 'Group Leader', 'Data Analyst', 'Admin Produksi', 'Pimpinan', 'Helper', 'Trafficman'];
        $units = ['Pit A', 'Pit B', 'Workshop', 'MASTER', 'Front Loading'];
        $roles = ['admin', 'pimpinan', 'group leader', 'helper', 'trafficman', 'tamu', 'operator'];

        return collect(range(0, count($names) - 1))->map(fn (int $index) => [
            'id' => $index + 1,
            'username' => $index === 0 ? 'admin' : (string) (10000 + $index),
            'name' => $names[$index],
            'jabatan' => $jabatans[$index % count($jabatans)],
            'unit' => $units[$index % count($units)],
            'role' => $index === 0 ? 'admin' : $roles[$index % count($roles)],
            'passwordStatus' => $index % 3 === 0 ? 'custom' : 'default',
        ])->all();
    }
}
