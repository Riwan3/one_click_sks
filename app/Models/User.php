<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

/**
 * Akun login. Data kepegawaian (jabatan, status, dst) ada di Employee —
 * relasi 0..1, tidak semua employee punya akun, dan users tidak menyimpan
 * data HR langsung.
 */
#[Fillable(['employee_id', 'username', 'name', 'email', 'password', 'jabatan', 'unit', 'lokasi', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('username', 'like', "%{$keyword}%")
                ->orWhere('name', 'like', "%{$keyword}%")
        );
    }

    public function isDefaultPassword(): bool
    {
        return Hash::check($this->username, $this->password);
    }

    public function isManagement(): bool
    {
        return $this->role === 'management';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }

    public function isGlManpower(): bool
    {
        return $this->role === 'gl_manpower';
    }
}
