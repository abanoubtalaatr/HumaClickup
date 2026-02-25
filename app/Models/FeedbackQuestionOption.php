<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackQuestionOption extends Model
{
    protected $table = 'feedback_question_options';

    protected $fillable = [
        'feedback_question_id',
        'option_text',
        'value',
        'order',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(FeedbackQuestion::class, 'feedback_question_id');
    }
}
