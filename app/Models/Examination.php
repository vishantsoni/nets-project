<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Examination extends Model
{
    protected $fillable = [
        'uuid', 'test_id', 'title', 'description', 'type', 'subject_id',
        'duration', 'total_marks', 'passing_marks', 'max_attempts',
        'negative_marking', 'negative_marks_ratio', 'shuffle_questions',
        'shuffle_options', 'show_result_immediately', 'result_visibility',
        'start_time', 'end_time', 'is_active', 'is_published',
        'institute_id', 'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'negative_marking' => 'boolean',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'show_result_immediately' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($exam) {
            if (! $exam->uuid) {
                $exam->uuid = (string) Str::uuid();
            }
            if (! $exam->test_id) {
                $year = now()->year;
                $lastId = self::max('id') + 1;
                $exam->test_id = 'TEST-' . $year . '-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
            ->withPivot(['section_id', 'sort_order', 'marks']);
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ExamSection::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function isActive(): bool
    {
        if (! $this->is_active || ! $this->is_published) {
            return false;
        }
        if ($this->start_time && now()->lt($this->start_time)) {
            return false;
        }
        if ($this->end_time && now()->gt($this->end_time)) {
            return false;
        }
        return true;
    }

    public function getQuestionCount(): int
    {
        return $this->questions()->count();
    }
}