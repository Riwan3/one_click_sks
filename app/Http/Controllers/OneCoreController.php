<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OneCoreController extends Controller
{
    private const DUMMY_PASSWORD = 'admin123';

    private const PER_PAGE = 10;

    public function __construct(private UserService $userService) {}

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

        $users = $this->userService->paginate($search, $page, self::PER_PAGE);

        return Inertia::render('OneCore/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => [
                'signature' => 2,
                'notification' => 3,
                'message' => 1,
            ],
            'menu' => config('one_core_menu'),
            'search' => $search,
            'users' => UserResource::collection($users->items())->resolve(),
            'pagination' => [
                'currentPage' => $users->currentPage(),
                'lastPage' => $users->lastPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('OneCore/Users/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => ['signature' => 2, 'notification' => 3, 'message' => 1],
            'menu' => config('one_core_menu'),
            'roles' => $this->roleOptions(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->create($request->validated());

        return redirect('/one-core')->with('success', 'User baru berhasil ditambahkan.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('OneCore/Users/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => ['signature' => 2, 'notification' => 3, 'message' => 1],
            'menu' => config('one_core_menu'),
            'roles' => $this->roleOptions(),
            'targetUser' => new UserResource($user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->update($user, $request->validated());

        return redirect('/one-core')->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->username === 'admin') {
            return redirect('/one-core')->with('error', 'Akun admin tidak bisa dihapus.');
        }

        $this->userService->delete($user);

        return redirect('/one-core')->with('success', 'User berhasil dihapus.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->userService->resetPasswordToDefault($user);

        return redirect('/one-core')->with('success', 'Password berhasil direset ke default (NRP).');
    }

    public function unlock(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required|string']);

        if ($request->input('password') !== self::DUMMY_PASSWORD) {
            $request->session()->put('one_core_error', 'Password otorisasi salah.');

            return redirect('/one-core');
        }

        $request->session()->put('one_core_unlocked', true);

        return redirect('/one-core');
    }

    public function lock(Request $request): RedirectResponse
    {
        $request->session()->forget('one_core_unlocked');

        return redirect('/one-core');
    }

    private function roleOptions(): array
    {
        return array_map(
            fn (UserRole $role) => ['value' => $role->value, 'label' => $role->label()],
            UserRole::cases()
        );
    }
}
