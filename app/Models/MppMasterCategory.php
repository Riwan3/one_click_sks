<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nama', 'kode', 'kategori_tipe', 'plan_mp', 'sort_order', 'active', 'created_by', 'updated_by'])]
class MppMasterCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'plan_mp' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function scopeStaff(Builder $query): Builder
    {
        return $query->where('kategori_tipe', 'staff');
    }

    public function scopePengawas(Builder $query): Builder
    {
        return $query->where('kategori_tipe', 'pengawas');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('nama', 'like', "%{$keyword}%")
                ->orWhere('kode', 'like', "%{$keyword}%")
        );
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('nama');
    }
}
