<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OMRResult extends Model
{
    protected $fillable = [
        'omr_sheet_id', 'question_id', 'selected_option',
        'is_correct', 'marks_obtained',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'marks_obtained' => 'decimal:2',
    ];

    public function omrSheet(): BelongsTo
    {
        return $this->belongsTo(OMRSheet::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}