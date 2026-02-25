<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestFeedbackAnswer extends Model
{
    protected $fillable = [
        'guest_feedback_submission_id',
        'feedback_question_id',
        'feedback_question_option_id',
        'rating_value',
    ];

    protected $casts = [
        'rating_value' => 'integer',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(GuestFeedbackSubmission::class, 'guest_feedback_submission_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(FeedbackQuestion::class, 'feedback_question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(FeedbackQuestionOption::class, 'feedback_question_option_id');
    }

    /** Get numeric value for scoring (option value or rating_value) */
    public function getScoreValue(): ?float
    {
        if ($this->feedback_question_option_id && $this->option) {
            return $this->option->value !== null ? (float) $this->option->value : null;
        }
        if ($this->rating_value !== null) {
            return (float) $this->rating_value;
        }
        return null;
    }
}
