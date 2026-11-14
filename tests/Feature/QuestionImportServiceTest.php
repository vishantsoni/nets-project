<?php

namespace Tests\Feature;

use App\Models\Examination;
use App\Models\Question;
use App\Models\Subject;
use App\Services\QuestionImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_mcq_options_and_skips_normalized_question_duplicates(): void
    {
        $subject = Subject::create(['name' => 'Mathematics']);
        $question = [
            'question_text' => 'What is 2 + 2?',
            'option_a' => '3',
            'option_b' => '4',
            'option_c' => '5',
            'option_d' => '6',
            'correct_option' => 'B',
            'explanation' => 'Two plus two is four.',
        ];
        $importer = app(QuestionImportService::class);

        $saved = $importer->import([$question], $subject->id, null, null, 'medium', false);
        $duplicate = $importer->import([
            [...$question, 'question_text' => ' WHAT IS 2, 2! '],
        ], $subject->id, null, null, 'medium', false);

        $this->assertSame(1, $saved['saved']);
        $this->assertSame(1, $duplicate['duplicates']);
        $this->assertSame(1, Question::count());
        $this->assertCount(4, Question::firstOrFail()->options);
        $this->assertSame('B', Question::firstOrFail()->options->firstWhere('is_correct', true)->option_value);
    }

    public function test_it_publishes_and_assigns_questions_when_a_target_exam_is_selected(): void
    {
        $subject = Subject::create(['name' => 'Science']);
        $exam = Examination::create([
            'title' => 'Practice Test',
            'subject_id' => $subject->id,
        ]);

        $result = app(QuestionImportService::class)->import([
            [
                'question_text' => 'Which planet is known as the Red Planet?',
                'option_a' => 'Earth',
                'option_b' => 'Mars',
                'option_c' => 'Venus',
                'option_d' => 'Jupiter',
                'correct_option' => 'B',
                'explanation' => 'Mars appears red due to iron oxide.',
            ],
        ], $subject->id, null, $exam->id, 'medium', true);

        $question = Question::firstOrFail();

        $this->assertSame(1, $result['saved']);
        $this->assertSame('published', $question->status);
        $this->assertSame($question->id, $exam->examQuestions()->value('question_id'));
    }
}
