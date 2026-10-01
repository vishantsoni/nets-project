<?php

namespace App\Models;

use App\Models\Concerns\ScopesToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OMRSheet extends Model
{
    use ScopesToInstitute;

    protected $table = 'omr_sheets';

    protected $fillable = [
        'examination_id', 'user_id', 'image_path', 'status', 'processed_data',
    ];

    protected $casts = [
        'processed_data' => 'array',
    ];

    protected static function instituteScopeColumn(): ?string
    {
        return null;
    }

    protected static function instituteScopeRelation(): ?string
    {
        return 'examination';
    }

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