<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'title',
        'type',
        'category',
        'description',
        'image_path',
        'reward_date',
    ];

    protected $casts = [
        'reward_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RewardRead::class);
    }
}
