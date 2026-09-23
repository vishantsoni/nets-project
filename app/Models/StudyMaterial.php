<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudyMaterial extends Model
{
    protected $fillable = [
        'title', 'description', 'subject_id', 'topic_id', 'type',
        'file_path', 'thumbnail', 'is_paid', 'price', 'is_published',
        'download_count', 'institute_id', 'uploaded_by',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_published' => 'boolean',
        'price' => 'decimal:2',
        'download_count' => 'integer',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'study_material_categories');
    }

    public function orderItems(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items', 'item_id', 'id')
            ->withPivot(['order_id', 'quantity', 'unit_price', 'total_price'])
            ->wherePivot('item_type', 'study_material');
    }

    public function getIsAccessibleByUser(User $user): bool
    {
        if (! $this->is_published) {
            return false;
        }

        if (! $this->is_paid) {
            return true;
        }

        return $this->orderItems()
            ->where('user_id', $user->id)
            ->exists();
    }
}