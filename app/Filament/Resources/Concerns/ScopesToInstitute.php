<?php

namespace App\Filament\Resources\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait ScopesToInstitute
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (method_exists(static::getModel(), 'scopeForInstitute')) {
            return $query->forInstitute(auth()->user());
        }

        return $query;
    }
}
