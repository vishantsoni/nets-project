<?php

namespace App\Models;

use App\Models\Concerns\ScopesToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Subject extends Model
{
    use ScopesToInstitute;

    protected $fillable = [
        'name', 'code', 'description', 'institute_id', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    public function topics(): HasManyThrough
    {
        return $this->hasManyThrough(Topic::class, Chapter::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function examinations(): HasMany
    {
        return $this->hasMany(Examination::class);
    }

    public function studyMaterials(): HasMany
    {
        return $this->hasMany(StudyMaterial::class);
    }
}