<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OMRSheet extends Model
{
    protected $fillable = [
        'examination_id', 'user_id', 'image_path', 'status', 'processed_data',
    ];

    protected $casts = [
        'processed_data' => 'array',
    ];

    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(OMRResult::class);
    }
}