<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingBoard extends Model
{
    use HasFactory;

    protected $fillable = [
        'site',
        'tanggal',
        'shift',
        'type',
        'image_path',
        'title',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
