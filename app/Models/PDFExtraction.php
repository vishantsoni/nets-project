<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PDFExtraction extends Model
{
    protected $table = 'pdf_extractions';

    protected $fillable = [
        'file_path', 'status', 'extracted_content', 'parsed_questions',
        'subject_id', 'topic_id', 'uploaded_by', 'error_message',
    ];

    protected $casts = [
        'parsed_questions' => 'array',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}