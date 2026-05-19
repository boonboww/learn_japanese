<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'alphabet_group_id', 'is_completed', 'completed_at'])]
class UserProgress extends Model
{
    use HasFactory;

    protected $table = 'user_progress';

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user associated with this progress.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the alphabet group associated with this progress.
     */
    public function alphabetGroup(): BelongsTo
    {
        return $this->belongsTo(AlphabetGroup::class);
    }
}
