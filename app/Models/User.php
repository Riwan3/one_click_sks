<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Akun login. Data kepegawaian (jabatan, status, dst) ada di Employee —
 * relasi 0..1, tidak semua employee punya akun, dan users tidak menyimpan
 * data HR langsung.
 */
class User extends Authenticatable
{
    protected $fillable = [
        'employee_id',
        'username',
        'name',
        'email',
        'password',
        'jabatan',
        'unit',
        'lokasi',
        'role',
        'foto',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
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
        return in_array(strtolower((string) $this->role), ['admin', 'superadmin', 'management'], true)
            || str_contains(strtolower((string) ($this->jabatan ?? '')), 'admin');
    }

    /**
     * Hak kelola Evaluasi Prosedur (assessment, tindak lanjut monitoring, sertifikat):
     * Admin, Pimpinan, Group Leader, Admin Produksi, PJO, Section/Dept Head, Manager.
     */
    public function canManageProcedureEvaluation(): bool
    {
        $roles = ['admin', 'pimpinan', 'group leader', 'group-leader', 'superadmin', 'admin produksi', 'pjo'];
        if (in_array(strtolower((string) $this->role), $roles, true)) return true;

        $jabatan = strtoupper((string) ($this->employee?->jabatan_ss6 ?? $this->jabatan ?? ''));

        return Str::contains($jabatan, ['GROUP LEADER', 'SECTION HEAD', 'DEPT HEAD', 'MANAGER', 'PJO']);
    }

    /** Akses modul Compliance One-Reporting (v1: admin, pimpinan, admin produksi, group leader). */
    public function canAccessComplianceReport(): bool
    {
        $roles = ['admin', 'superadmin', 'pimpinan', 'admin produksi', 'group leader', 'group-leader', 'gl_manpower'];

        return in_array(strtolower((string) $this->role), $roles, true);
    }

    /** Setting GL One-Reporting: hanya Admin & Admin Produksi (sama seperti v1). */
    public function canManageGlSetting(): bool
    {
        return $this->isAdmin() || strtolower((string) $this->role) === 'admin produksi';
    }

    /** Kelola assignment & centang task compliance; Group Leader biasa hanya melihat (kecuali GL Manpower), sama seperti v1. */
    public function canManageComplianceAssignment(): bool
    {
        $isPlainGroupLeader = in_array(strtolower((string) $this->role), ['group leader', 'group-leader'], true);

        return $this->canAccessComplianceReport()
            && (! $isPlainGroupLeader || $this->isGlManpower());
    }

    /**
     * Hak akses untuk melihat semua roster karyawan (pilih karyawan lain di Roster Personal & akses Roster Departemen).
     * Sesuai aturan: Admin, Software Engineer, Section Head, Dept Head.
     */
    public function canViewAllRosters(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $role = strtolower((string) ($this->role ?? ''));
        $jabatanUser = strtolower((string) ($this->jabatan ?? ''));
        $jabatanEmp = strtolower((string) ($this->employee?->jabatan_ss6 ?? ''));
        $jabatan = trim($jabatanUser . ' ' . $jabatanEmp);

        // 1. Admin, Superadmin, Management
        if (in_array($role, ['admin', 'superadmin', 'management', 'developer', 'it', 'software_engineer'], true)) {
            return true;
        }

        // 2. Software Engineer / IT / Developer / Programmer
        if (str_contains($role, 'software') || str_contains($role, 'engineer') || str_contains($role, 'developer') || str_contains($role, 'programmer')
            || str_contains($jabatan, 'software') || str_contains($jabatan, 'engineer') || str_contains($jabatan, 'developer') || str_contains($jabatan, 'programmer')
            || str_contains($jabatan, 'it ') || str_contains($jabatan, 'ict')) {
            return true;
        }

        // 3. Section Head / Section
        if (str_contains($role, 'section') 
            || str_contains($jabatan, 'section head') 
            || str_contains($jabatan, 'section') 
            || str_contains($jabatan, 'sh ') 
            || str_ends_with($jabatan, ' sh') 
            || $jabatanUser === 'sh' 
            || $jabatanEmp === 'sh') {
            return true;
        }

        // 4. Dept Head / Department Head / Pimpinan
        if (str_contains($role, 'dept') 
            || str_contains($role, 'pimpinan')
            || str_contains($jabatan, 'dept head') 
            || str_contains($jabatan, 'department head') 
            || str_contains($jabatan, 'dh ') 
            || str_ends_with($jabatan, ' dh') 
            || $jabatanUser === 'dh' 
            || $jabatanEmp === 'dh') {
            return true;
        }

        return false;
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereIn('role', ['admin', 'superadmin', 'management'])
                ->orWhere('jabatan', 'like', '%admin%');
        });
    }

    public function hasRole(string $role): bool
    {
        return strtolower((string) $this->role) === strtolower($role);
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }

    /** GL Manpower dideteksi dari jabatan (sama seperti v1), bukan dari kolom role. */
    public function isGlManpower(): bool
    {
        $jabatanStr = strtoupper((string) ($this->employee?->jabatan_ss6 ?? $this->jabatan ?? ''));

        return str_contains($jabatanStr, 'GROUP LEADER MANPOWER')
            || str_contains($jabatanStr, 'GL MANPOWER')
            || $this->isAdmin();
    }

    public function isGroupLeader(): bool
    {
        $roleStr = strtolower((string) ($this->role ?? ''));
        $jabatanStr = strtoupper((string) ($this->employee?->jabatan_ss6 ?? $this->jabatan ?? ''));

        return in_array($roleStr, ['group-leader', 'groupleader', 'gl'], true)
            || str_contains($roleStr, 'group-leader')
            || str_contains($roleStr, 'group leader')
            || str_contains($jabatanStr, 'GROUP LEADER')
            || str_contains($jabatanStr, 'GL ')
            || str_ends_with($jabatanStr, ' GL')
            || $jabatanStr === 'GL';
    }

    public function getEmployeeRecord(): ?Employee
    {
        if ($this->relationLoaded('employee') && $this->employee) {
            return $this->employee;
        }

        if ($this->employee_id) {
            $emp = Employee::find($this->employee_id);
            if ($emp) {
                return $emp;
            }
        }

        if ($this->username) {
            $emp = Employee::where('nrp', $this->username)->first();
            if ($emp) {
                if (! $this->employee_id) {
                    $this->update(['employee_id' => $emp->id]);
                }

                return $emp;
            }
        }

        if ($this->name) {
            $emp = Employee::where('name', $this->name)->first();
            if ($emp) {
                if (! $this->employee_id) {
                    $this->update(['employee_id' => $emp->id]);
                }

                return $emp;
            }
        }

        return null;
    }

    public function isMedic(): bool
    {
        return $this->role === 'medic';
    }

    /**
     * Akses fitur Data Unfit Karyawan (Operator Unfit) — setara batasan role
     * di V1 (lihat OperatorUnfitController lama): admin, pimpinan, group
     * leader, dan medic. Role lain (termasuk operator biasa) tidak boleh
     * lihat/kelola data unfit sama sekali.
     */
    public function canAccessUnfitData(): bool
    {
        return in_array($this->role, ['admin', 'pimpinan', 'group-leader', 'medic', 'management'], true);
    }

    public function canAccessRekapPemulangan(): bool
    {
        return in_array($this->role, ['admin', 'pimpinan', 'group-leader', 'medic', 'management'], true);
    }

    /** Kelola penuh modul Assessment Best Employee: kegiatan, soal, peserta, reset attempt, review nilai manual. */
    public function canManageBestEmployee(): bool
    {
        return $this->isAdmin();
    }

    /** Lihat monitoring & hasil Assessment Best Employee (tanpa hak kelola). */
    public function canViewBestEmployeeResults(): bool
    {
        return $this->isAdmin() || $this->isPimpinan();
    }
}
