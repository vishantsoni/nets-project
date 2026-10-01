<?php

namespace App\Models;

use App\Models\Concerns\ScopesToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Topic extends Model
{
    use ScopesToInstitute;

    protected $fillable = [
        'subject_id', 'name', 'description', 'standard_marks', 'time_allocation', 'is_active',
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
        return 'subject';
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_topics');
    }
}