<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'nrp',
        'name',
        'jabatan_ss6',
        'jabatan_roster',
        'status_karyawan',
        'lokasi_kerja',
        'unit',
        'no_telepon',
        'efektif_bekerja',
        'input_here',
        'email',
    ];

    protected function casts(): array
    {
        return [
            'status_karyawan' => EmployeeStatus::class,
            'efektif_bekerja' => 'date',
        ];
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function operatorUnfitRecords()
    {
        return $this->hasMany(OperatorUnfit::class, 'nrp', 'nrp');
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('nrp', 'like', "%{$keyword}%")
                ->orWhere('name', 'like', "%{$keyword}%")
        );
    }

    public function scopeJabatan(Builder $query, ?string $jabatan): Builder
    {
        return $jabatan ? $query->where('jabatan_ss6', $jabatan) : $query;
    }

    public function scopeActiveOnly(Builder $query): Builder
    {
        return $query->where('status_karyawan', '!=', EmployeeStatus::Inactive->value);
    }

    public function scopeInactiveOnly(Builder $query): Builder
    {
        return $query->where('status_karyawan', EmployeeStatus::Inactive->value);
    }

    public function tenureLabel(): string
    {
        if (! $this->efektif_bekerja) {
            return '-';
        }

        if ($this->efektif_bekerja->isFuture()) {
            return 'Belum mulai';
        }

        $diff = $this->efektif_bekerja->diff(now());

        $parts = [];
        if ($diff->y > 0) $parts[] = "{$diff->y} Thn";
        if ($diff->m > 0) $parts[] = "{$diff->m} Bln";
        if (! $parts) $parts[] = "{$diff->d} Hari";

        return implode(' ', $parts);
    }
}
