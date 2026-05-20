<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'order_index'])]
class AlphabetGroup extends Model
{
    use HasFactory;

    /**
     * Get the individual alphabet letters for this group.
     */
    public function alphabets(): HasMany
    {
        return $this->hasMany(Alphabet::class, 'group_id');
    }

    /**
     * Get the vocabularies associated with this group.
     */
    public function vocabularies(): HasMany
    {
        return $this->hasMany(Vocabulary::class, 'group_id');
    }

    /**
     * Get the quizzes associated with this group.
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'alphabet_group_id');
    }

    /**
     * Get the user progress records for this group.
     */
    public function progresses(): HasMany
    {
        return $this->hasMany(UserProgress::class, 'alphabet_group_id');
    }
}
