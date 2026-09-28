<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProsedurCategory extends Model
{
    use HasFactory;

    protected $table = 'prosedur_categories';

    public $timestamps = false;

    protected $fillable = [
        'slug',
        'name',
    ];

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(
            ProsedurKerja::class,
            'prosedur_category_docs',
            'category_id',
            'prosedur_id'
        );
    }
}
