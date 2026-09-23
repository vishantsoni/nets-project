<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Question extends Model
{
    protected $fillable = [
        'uuid', 'question_text', 'question_type', 'subject_id', 'topic_id',
        'difficulty', 'marks', 'negative_marks', 'explanation', 'status',
        'is_ai_generated', 'correct_answer_text', 'institute_id', 'created_by',
    ];

    protected $casts = [
        'is_ai_generated' => 'boolean',
        'marks' => 'integer',
        'negative_marks' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($question) {
            if (! $question->uuid) {
                $question->uuid = (string) Str::uuid();
            }
        });
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'question_topics');
    }

    public function examinations(): BelongsToMany
    {
        return $this->belongsToMany(Examination::class, 'exam_questions');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('question_type', $type);
    }

    public function getCorrectOptions()
    {
        return $this->options()->where('is_correct', true)->get();
    }
}