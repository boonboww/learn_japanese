<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['alphabet_group_id', 'title'])]
class Quiz extends Model
{
    use HasFactory;

    /**
     * Get the alphabet group this quiz tests.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(AlphabetGroup::class, 'alphabet_group_id');
    }

    /**
     * Get the questions contained in this quiz.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
