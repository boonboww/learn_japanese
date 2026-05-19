<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['group_id', 'character', 'romaji', 'audio_url', 'stroke_path'])]
class Alphabet extends Model
{
    use HasFactory;

    /**
     * Get the alphabet group this character belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(AlphabetGroup::class, 'group_id');
    }
}
