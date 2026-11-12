<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAnswer extends Model
{
    protected $table = 'student_answers';

    protected $fillable = [
        'attempt_id', 'question_id', 'selected_option_id',
        'answer_text', 'marks_obtained', 'is_correct', 'answered_at_seconds',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'marks_obtained' => 'decimal:2',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }

    public function getStudentAnswerAttribute()
    {
        $question = $this->question;
        if (! $question) {
            return $this->answer_text;
        }

        if ($question->question_type === 'mcq') {
            if ($this->selectedOption) {
                return $this->selectedOption->option_text;
            }
            $option = QuestionOption::find($this->answer_text);

            return $option ? $option->option_text : $this->answer_text;
        }

        if ($question->question_type === 'true_false') {
            if ($this->selectedOption) {
                return $this->selectedOption->option_text;
            }

            return $this->answer_text ? ucfirst($this->answer_text) : '';
        }

        return $this->answer_text ?? '';
    }

    public function getCorrectAnswerDisplayAttribute()
    {
        $question = $this->question;
        if (! $question) {
            return '';
        }

        if ($question->question_type === 'mcq') {
            $correctOptions = $question->options()->where('is_correct', true)->get();

            return $correctOptions->pluck('option_text')->implode(', ');
        }

        if ($question->question_type === 'true_false') {
            $correct = $question->options()->where('is_correct', true)->first();

            return $correct ? $correct->option_text : '';
        }

        return $question->correct_answer_text ?? '';
    }
}
