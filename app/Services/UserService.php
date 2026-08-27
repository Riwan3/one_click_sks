<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function paginate(?string $search, int $page, int $perPage): LengthAwarePaginator
    {
        return User::query()
            ->search($search)
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function resetPasswordToDefault(User $user): User
    {
        $user->update(['password' => $user->username]);

        return $user;
    }
}
