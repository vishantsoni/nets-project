<?php

namespace Tests\Unit;

use App\Services\QuestionTextNormalizer;
use PHPUnit\Framework\TestCase;

class QuestionTextNormalizerTest extends TestCase
{
    public function test_it_hashes_question_text_independently_of_case_and_punctuation(): void
    {
        $first = QuestionTextNormalizer::hash('What is 2 + 2?');
        $second = QuestionTextNormalizer::hash('  WHAT   IS 2, 2! ');

        $this->assertSame($first, $second);
    }

    public function test_it_preserves_unicode_letters_when_normalizing_questions(): void
    {
        $this->assertSame(
            QuestionTextNormalizer::hash('भारत की राजधानी क्या है?'),
            QuestionTextNormalizer::hash('भारत की राजधानी क्या है।'),
        );
    }
}
