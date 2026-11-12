<?php

namespace Tests\Unit;

use App\Services\PdfExtraction\McqQuestionParser;
use PHPUnit\Framework\TestCase;

class McqQuestionParserTest extends TestCase
{
    public function test_it_parses_multiple_choice_questions_and_a_trailing_answer_key(): void
    {
        $content = <<<'TEXT'
1. What is 2 + 2?
A) 3
B) 4
C) 5
D) 6

2. Which number is even?
A. 3
B. 5
C. 8
D. 9

Answer Key
1. B
2. C
TEXT;

        $questions = (new McqQuestionParser)->parse($content);

        $this->assertCount(2, $questions);
        $this->assertSame('What is 2 + 2?', $questions[0]['question_text']);
        $this->assertSame('B', $questions[0]['options'][1]['option_value']);
        $this->assertTrue($questions[0]['options'][1]['is_correct']);
        $this->assertFalse($questions[0]['options'][0]['is_correct']);
        $this->assertTrue($questions[0]['selected']);
        $this->assertTrue($questions[1]['options'][2]['is_correct']);
    }

    public function test_it_reads_inline_answers(): void
    {
        $content = <<<'TEXT'
Q1. Which is a prime number?
(A) 4
(B) 5
(C) 6
Answer: B
TEXT;

        $questions = (new McqQuestionParser)->parse($content);

        $this->assertCount(1, $questions);
        $this->assertSame('Which is a prime number?', $questions[0]['question_text']);
        $this->assertTrue($questions[0]['options'][1]['is_correct']);
        $this->assertTrue($questions[0]['selected']);
    }

    public function test_it_leaves_questions_without_an_answer_unselected_for_review(): void
    {
        $content = <<<'TEXT'
1. Which number is prime?
A) 4
B) 5
C) 6
TEXT;

        $questions = (new McqQuestionParser)->parse($content);

        $this->assertCount(1, $questions);
        $this->assertFalse($questions[0]['selected']);
        $this->assertFalse($questions[0]['options'][0]['is_correct']);
    }

    public function test_it_parses_options_that_share_a_line_with_the_question(): void
    {
        $content = <<<'TEXT'
1. What is 2 + 2? (A) 3 (B) 4 (C) 5 (D) 6
Answer: B
TEXT;

        $questions = (new McqQuestionParser)->parse($content);

        $this->assertCount(1, $questions);
        $this->assertSame('What is 2 + 2?', $questions[0]['question_text']);
        $this->assertSame(['3', '4', '5', '6'], array_column($questions[0]['options'], 'option_text'));
        $this->assertTrue($questions[0]['options'][1]['is_correct']);
    }
}
