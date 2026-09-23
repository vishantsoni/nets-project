<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSection extends Model
{
    protected $fillable = [
        'examination_id', 'title', 'description', 'question_count', 'marks_per_question',
    ];

    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class);
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class, 'section_id');
    }
}