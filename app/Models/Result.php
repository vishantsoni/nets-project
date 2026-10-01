<?php

namespace App\Models;

use App\Models\Concerns\ScopesToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    use ScopesToInstitute;

    protected $fillable = [
        'attempt_id', 'total_questions', 'attempted_questions', 'correct_answers',
        'incorrect_answers', 'unanswered_questions', 'total_marks', 'obtained_marks',
        'percentage', 'is_passed', 'time_taken', 'rank_position', 'subject_wide_performance', 'topic_wide_performance',
    ];

    protected $casts = [
        'total_marks' => 'decimal:2',
        'obtained_marks' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_passed' => 'boolean',
        'subject_wide_performance' => 'array',
        'topic_wide_performance' => 'array',
    ];

    protected static function instituteScopeColumn(): ?string
    {
        return null;
    }

    protected static function instituteScopeRelation(): ?string
    {
        return 'attempt.examination';
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }
}