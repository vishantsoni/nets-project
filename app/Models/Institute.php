<?php

namespace App\Models;

use App\Models\Concerns\ScopesToInstitute;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institute extends Model
{
    use ScopesToInstitute;

    protected $fillable = [
        'name', 'slug', 'description', 'address', 'phone', 'email',
        'website', 'logo', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeForInstitute(Builder $query, ?User $user): Builder
    {
        if (! $user || $user->isPlatformAdmin()) {
            return $query;
        }

        if (blank($user->institute_id)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereKey($user->institute_id);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function examinations(): HasMany
    {
        return $this->hasMany(Examination::class);
    }

    public function studyMaterials(): HasMany
    {
        return $this->hasMany(StudyMaterial::class);
    }

    public function b2bEnquiries(): HasMany
    {
        return $this->hasMany(B2BEnquiry::class);
    }
}