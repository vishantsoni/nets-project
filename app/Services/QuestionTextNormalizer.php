<?php

namespace App\Services;

class QuestionTextNormalizer
{
    public static function hash(string $questionText): ?string
    {
        $normalized = mb_strtolower($questionText, 'UTF-8');
        $normalized = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $normalized);
        $normalized = trim(preg_replace('/\s+/u', ' ', $normalized ?? ''));

        return $normalized === '' ? null : hash('sha256', $normalized);
    }
}
