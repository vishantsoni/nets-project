<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'total_amount', 'discount_amount',
        'tax_amount', 'currency', 'status', 'payment_status', 'payment_method',
        'transaction_id', 'shipping_address', 'billing_address', 'notes', 'ordered_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'ordered_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            if (! $order->order_number) {
                $order->order_number = 'ORD-' . now()->year . '-' . strtoupper(Str::random(8));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}