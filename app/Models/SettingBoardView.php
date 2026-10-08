<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingBoardView extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'board_type', 'work_date', 'shift', 'viewed_at'];

    protected function casts(): array
    {
        return ['work_date' => 'date', 'viewed_at' => 'datetime'];
    }
}
