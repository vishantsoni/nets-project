<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIExamGeneration extends Model
{
    protected $table = 'ai_generations';

    protected $fillable = [
        'subject_id', 'topic_id', 'question_type', 'count', 'difficulty',
        'prompt', 'generated_questions', 'status', 'created_by', 'error_message',
    ];

    protected $casts = [
        'generated_questions' => 'array',
        'count' => 'integer',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}