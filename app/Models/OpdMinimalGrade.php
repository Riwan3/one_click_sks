<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OpdMinimalGrade extends Model
{
    protected $table = 'opd_minimal_grade';

    protected $fillable = ['unit', 'grade_min_max', 'grade_min', 'cluster_unit', 'created_by'];

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        $keyword = trim((string) $keyword);
        if ($keyword === '') {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q
            ->where('unit', 'like', "%{$keyword}%")
            ->orWhere('grade_min_max', 'like', "%{$keyword}%")
            ->orWhere('grade_min', 'like', "%{$keyword}%")
            ->orWhere('cluster_unit', 'like', "%{$keyword}%"));
    }
}
