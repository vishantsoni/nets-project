<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class B2BEnquiryItem extends Model
{
    protected $table = 'b2b_enquiry_items';

    protected $fillable = [
        'enquiry_id', 'item_name', 'quantity', 'remarks',
    ];

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(B2BEnquiry::class);
    }
}