<?php

namespace App\Models;

use App\Models\Concerns\ScopesToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Topic extends Model
{
    use ScopesToInstitute;

    protected $fillable = [
        'chapter_id', 'name', 'description', 'standard_marks', 'time_allocation', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'standard_marks' => 'integer',
        'time_allocation' => 'integer',
    ];

    protected static function instituteScopeColumn(): ?string
    {
        return null;
    }

    protected static function instituteScopeRelation(): ?string
    {
        return 'chapter.subject';
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_topics');
    }
}