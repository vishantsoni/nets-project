<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait ScopesToInstitute
{
    public static function bootScopesToInstitute(): void
    {
        static::creating(function ($model) {
            $user = auth()->user();

            if (! $user instanceof User || $user->isPlatformAdmin()) {
                return;
            }

            if ($model->instituteScopeColumn() === null || filled($model->{$model->instituteScopeColumn()})) {
                return;
            }

            $model->{$model->instituteScopeColumn()} = $user->institute_id;
        });
    }

    public function scopeForInstitute(Builder $query, ?User $user): Builder
    {
        if (! $user || $user->isPlatformAdmin()) {
            return $query;
        }

        if (blank($user->institute_id)) {
            return $query->whereRaw('1 = 0');
        }

        $instituteId = $user->institute_id;

        $column = static::instituteScopeColumn();

        if ($column !== null) {
            return $query->where($this->getTable() . '.' . $column, $instituteId);
        }

        $relation = static::instituteScopeRelation();

        if ($relation === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas(
            $relation,
            fn (Builder $related) => $related->where('institute_id', $instituteId)
        );
    }

    /**
     * Column on this model's own table that holds the owning institute id.
     */
    protected static function instituteScopeColumn(): ?string
    {
        return 'institute_id';
    }

    /**
     * Relation used to reach the owning institute when the table has no
     * institute_id column of its own.
     */
    protected static function instituteScopeRelation(): ?string
    {
        return null;
    }
}
