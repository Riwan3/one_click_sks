<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function paginate(?string $search, int $page, int $perPage): LengthAwarePaginator
    {
        return User::query()
            ->search($search)
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): User
    {
        $data['email'] = $data['username'].'@oneclick.local';
        $data['password'] = Hash::make(($data['password'] ?? '') ?: $data['username']);

        return User::create($data);
    }

    /**
     * Buat/perbarui akun login berdasarkan data karyawan — dipanggil dari
     * EmployeeService setiap kali Data Karyawan ditambah/diubah, supaya NRP
     * yang baru didata otomatis punya akun (password default = NRP).
     */
    public function syncFromEmployee(Employee $employee, ?string $previousUsername = null): User
    {
        $user = User::where('username', $previousUsername ?? $employee->nrp)->first();

        $attributes = [
            'username' => $employee->nrp,
            'name' => $employee->name,
            'jabatan' => $employee->jabatan_ss6,
            'unit' => $employee->unit,
            'lokasi' => $employee->lokasi_kerja,
        ];

        if ($user) {
            $user->update($attributes);

            return $user;
        }

        $attributes['role'] = UserRole::fromJabatan($employee->jabatan_ss6)->value;

        return $this->create($attributes);
    }

    public function update(User $user, array $data): User
    {
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function resetPasswordToDefault(User $user): User
    {
        $user->update(['password' => Hash::make($user->username)]);

        return $user;
    }
}
