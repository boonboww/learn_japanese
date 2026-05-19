<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'streak_count', 'last_active_date'])]
class UserStreak extends Model
{
    use HasFactory;

    protected $casts = [
        'last_active_date' => 'date',
    ];

    /**
     * Get the user that owns the streak (1:1 reverse relation).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
