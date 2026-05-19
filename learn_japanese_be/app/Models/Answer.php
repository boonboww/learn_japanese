<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['question_id', 'answer_text', 'is_correct'])]
class Answer extends Model
{
    use HasFactory;

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /**
     * Get the question this answer option belongs to.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
