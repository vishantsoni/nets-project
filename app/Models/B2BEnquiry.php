<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class B2BEnquiry extends Model
{
    protected $table = 'b2b_enquiries';

    protected $fillable = [
        'enquiry_number', 'name', 'email', 'phone', 'company', 'designation',
        'subject', 'description', 'status', 'assigned_to', 'institute_id', 'followed_up_at',
    ];

    protected $casts = [
        'followed_up_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($enquiry) {
            if (! $enquiry->enquiry_number) {
                $enquiry->enquiry_number = 'ENQ-' . now()->year . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(B2BEnquiryItem::class);
    }
}