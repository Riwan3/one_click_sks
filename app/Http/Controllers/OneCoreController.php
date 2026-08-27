<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleMenuAccessRequest;
use App\Http\Requests\UpdateRolePermissionRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Services\RolePermissionService;
use App\Services\UserService;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OneCoreController extends Controller
{
    private const DUMMY_PASSWORD = 'admin123';

    private const PER_PAGE = 10;

    public function __construct(
        private UserService $userService,
        private RolePermissionService $rolePermissionService,
    ) {}

    /**
     * Kelola role & password akun login. Data kepegawaian (nama, jabatan, unit, dst)
     * dikelola terpisah dari One-Power > Data Karyawan — tidak semua karyawan
     * punya akun login, jadi "tambah user" tetap manual di sini.
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

        $users = $this->userService->paginate($search, $page, self::PER_PAGE);

        return Inertia::render('OneCore/Dashboard', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_core_menu'),
            'search' => $search,
            'users' => UserResource::collection($users->items())->resolve(),
            'roles' => $this->roleOptions(),
            'pagination' => [
                'currentPage' => $users->currentPage(),
                'lastPage' => $users->lastPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function updateUserRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $user->update(['role' => $request->string('role')->toString()]);

        return redirect('/one-core')->with('success', 'Role akses user berhasil diperbarui.');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'name' => 'required|string|max:255',
            'role' => 'required|string',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:6',
        ]);

        User::create([
            'username' => $validated['username'],
            'name' => $validated['name'],
            'role' => $validated['role'],
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'] ?: $validated['username'],
        ]);

        return redirect('/one-core')->with('success', 'User baru berhasil ditambahkan.');
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

    public function roles(): Response
    {
        return Inertia::render('OneCore/Roles/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_core_menu'),
            'roles' => $this->rolePermissionService->summaryForAllRoles(),
        ]);
    }

    public function storeRole(StoreRoleRequest $request): RedirectResponse
    {
        $this->rolePermissionService->createRole(
            $request->string('code')->toString(),
            $request->string('label')->toString(),
            $request->string('scope')->toString(),
        );

        return redirect('/one-core/roles')->with('success', 'Role baru berhasil ditambahkan.');
    }

    public function destroyRole(string $role): RedirectResponse
    {
        $roleModel = Role::where('code', $role)->firstOrFail();

        $this->rolePermissionService->deleteRole($roleModel);

        return redirect('/one-core/roles')->with('success', "Role {$roleModel->label} berhasil dihapus.");
    }

    public function updateRole(UpdateRolePermissionRequest $request, string $role): RedirectResponse
    {
        $roleModel = Role::where('code', $role)->firstOrFail();

        $this->rolePermissionService->update($roleModel->code, $request->boolean('is_mobile'), $request->input('module_keys', []));

        return redirect('/one-core/roles')->with('success', "Akses role {$roleModel->label} berhasil diperbarui.");
    }

    public function roleDetail(string $role): Response
    {
        $roleModel = Role::where('code', $role)->firstOrFail();

        return Inertia::render('OneCore/Roles/Detail', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_core_menu'),
            'role' => ['value' => $roleModel->code, 'label' => $roleModel->label, 'scope' => $roleModel->scope->value],
            'moduleMenus' => $this->rolePermissionService->menuDetailForRole($roleModel->code),
        ]);
    }

    public function updateRoleMenuAccess(UpdateRoleMenuAccessRequest $request, string $role, string $module): RedirectResponse
    {
        $roleModel = Role::where('code', $role)->firstOrFail();

        $this->rolePermissionService->updateMenuAccess($roleModel->code, $module, $request->input('menu_keys', []));

        return redirect("/one-core/roles/{$role}")->with('success', 'Akses menu berhasil diperbarui.');
    }

    private function roleOptions(): array
    {
        return Role::query()->orderBy('label')->get()->map(
            fn (Role $role) => ['value' => $role->code, 'label' => $role->label, 'scope' => $role->scope->value]
        )->all();
    }
}
