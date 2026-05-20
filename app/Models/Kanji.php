<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['character', 'meaning', 'onyomi', 'kunyomi', 'stroke_path'])]
class Kanji extends Model
{
    use HasFactory;

    protected $table = 'kanjis';

    /**
     * Get the vocabularies that feature this Kanji character (N:N relation).
     */
    public function vocabularies(): BelongsToMany
    {
        return $this->belongsToMany(Vocabulary::class, 'vocabulary_kanji');
    }
}
