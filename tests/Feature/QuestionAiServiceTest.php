<?php

namespace Tests\Feature;

use App\Ai\Agents\QuestionGenerationAgent;
use App\Services\PdfExtraction\PdfContentExtractor;
use App\Services\QuestionAiService;
use Mockery\MockInterface;
use Tests\TestCase;

class QuestionAiServiceTest extends TestCase
{
    public function test_it_generates_exact_question_batches_with_structured_ai_responses(): void
    {
        QuestionGenerationAgent::fake([
            ['questions' => array_fill(0, 10, $this->question('Question one'))],
            ['questions' => [$this->question('Question eleven')]],
        ]);

        $questions = app(QuestionAiService::class)->generateQuestions(
            'algebra',
            'hard',
            11,
            'Hindi',
        );

        $this->assertCount(11, $questions);
        $this->assertSame('A', $questions[0]['correct_option']);
        QuestionGenerationAgent::assertPromptedTimes(2);
        QuestionGenerationAgent::assertNotPrompted(
            fn ($prompt): bool => str_contains($prompt->prompt, 'Chapter context:')
        );
    }

    public function test_it_includes_the_optional_chapter_in_the_generation_prompt(): void
    {
        QuestionGenerationAgent::fake([
            ['questions' => [$this->question('A linear equation question')]],
        ]);

        $questions = app(QuestionAiService::class)->generateQuestions(
            'linear equations',
            'medium',
            1,
            'English',
            'Algebra',
        );

        $this->assertCount(1, $questions);
        QuestionGenerationAgent::assertPrompted(
            fn ($prompt): bool => str_contains($prompt->prompt, 'Chapter context: Algebra.')
        );
    }

    public function test_it_splits_pdf_text_into_ai_chunks_and_removes_overlap_duplicates(): void
    {
        $this->mock(PdfContentExtractor::class, function (MockInterface $mock): void {
            $mock->shouldReceive('extract')
                ->once()
                ->with('pdf-extractions/sample.pdf')
                ->andReturn(str_repeat('x', 3201));
        });

        QuestionGenerationAgent::fake([
            ['questions' => [$this->question('भारत का प्रश्न?')]],
            ['questions' => [$this->question('भारत का प्रश्न!')]],
        ]);

        $questions = app(QuestionAiService::class)->extractFromPdf('pdf-extractions/sample.pdf');

        $this->assertCount(1, $questions);
        QuestionGenerationAgent::assertPromptedTimes(2);
    }

    public function test_it_preserves_pdf_equations_in_renderable_latex_format(): void
    {
        QuestionGenerationAgent::fake([
            ['questions' => [$this->question('Solve $x^2 + y^2 = z^2$.')]],
        ]);

        $questions = app(QuestionAiService::class)->extractQuestionsFromText(
            "1. Solve x^2 + y^2 = z^2.\nA. 1\nB. 2\nC. 3\nD. 4"
        );

        $this->assertSame('Solve $x^2 + y^2 = z^2$.', $questions[0]['question_text']);
        QuestionGenerationAgent::assertPrompted(
            fn ($prompt): bool => str_contains($prompt->prompt, 'Preserve every mathematical or scientific equation')
                && str_contains($prompt->prompt, 'single-dollar delimiters')
        );
    }

    /**
     * @return array<string, string>
     */
    private function question(string $text): array
    {
        return [
            'question_text' => $text,
            'option_a' => 'पहला',
            'option_b' => 'दूसरा',
            'option_c' => 'तीसरा',
            'option_d' => 'चौथा',
            'correct_option' => 'A',
            'explanation' => 'कारण',
        ];
    }
}
