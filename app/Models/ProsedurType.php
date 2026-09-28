<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProsedurType extends Model
{
    use HasFactory;

    protected $table = 'prosedur_types';

    public $timestamps = false;

    protected $fillable = [
        'slug',
        'name',
    ];
}
