<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewardRead extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'reward_id',
        'user_id',
        'seen_at',
    ];

    protected $casts = [
        'seen_at' => 'datetime',
    ];

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
