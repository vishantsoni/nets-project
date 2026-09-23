<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Topic extends Model
{
    protected $fillable = [
        'subject_id', 'name', 'description', 'standard_marks', 'time_allocation', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'standard_marks' => 'integer',
        'time_allocation' => 'integer',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_topics');
    }
}