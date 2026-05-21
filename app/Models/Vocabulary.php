<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['group_id', 'word', 'furigana', 'romaji', 'meaning', 'type'])]
class Vocabulary extends Model
{
    use HasFactory;

    protected $table = 'vocabularies';

    /**
     * Get the alphabet group this vocabulary word belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(AlphabetGroup::class, 'group_id');
    }

    /**
     * Get the Kanji characters used in this vocabulary word (N:N relation).
     */
    public function kanjis(): BelongsToMany
    {
        return $this->belongsToMany(Kanji::class, 'vocabulary_kanji');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_vocabularies')
            ->withPivot('is_bookmarked', 'status')
            ->withTimestamps();
    }
}
