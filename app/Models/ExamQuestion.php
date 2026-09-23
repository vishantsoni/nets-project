<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestion extends Model
{
    protected $table = 'exam_questions';

    protected $fillable = [
        'examination_id', 'question_id', 'section_id', 'sort_order', 'marks',
    ];

    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ExamSection::class, 'section_id');
    }
}