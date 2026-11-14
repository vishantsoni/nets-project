<?php

namespace App\Services;

use App\Ai\Agents\QuestionGenerationAgent;
use App\Services\PdfExtraction\PdfContentExtractor;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Responses\StructuredAgentResponse;
use RuntimeException;

class QuestionAiService
{
    private const CHUNK_SIZE = 3000;

    private const CHUNK_OVERLAP = 200;

    public function __construct(private PdfContentExtractor $pdfContentExtractor) {}

    /**
     * @return array<int, array{question_text: string, option_a: string, option_b: string, option_c: string, option_d: string, correct_option: string, explanation: string}>
     */
    public function extractFromPdf(string $filePath): array
    {
        return $this->extractAndParsePdf($filePath)['questions'];
    }

    /**
     * @return array{text: string, questions: array<int, array<string, string>>}
     */
    public function extractAndParsePdf(string $filePath): array
    {
        $text = $this->pdfContentExtractor->extract($filePath);

        return [
            'text' => $text,
            'questions' => $this->extractQuestionsFromText($text),
        ];
    }

    /**
     * @return array<int, array{question_text: string, option_a: string, option_b: string, option_c: string, option_d: string, correct_option: string, explanation: string}>
     */
    public function extractQuestionsFromText(string $text): array
    {
        if (trim($text) === '') {
            throw new RuntimeException('No readable text was found in the PDF.');
        }

        $questions = [];

        foreach ($this->splitIntoChunks($text) as $chunk) {
            $response = $this->prompt(
                'Extract only multiple-choice questions that are present in this PDF text. The text may be messy or bilingual (Hindi and English). Preserve the original language and wording as closely as possible. Do not translate bilingual content. '.
                'Preserve every mathematical or scientific equation from the source in the question, options, and explanation where it appears. Format equations as inline LaTeX using single-dollar delimiters, such as $x^2 + y^2 = z^2$, so they render in the review preview. Do not omit, simplify, or invent equations; if symbols are unclear, keep only what can be read without guessing. '.
                'Include a question only when four options and exactly one correct answer can be identified; otherwise omit it. Do not infer an answer. Preserve explanations only when present; otherwise return an empty explanation string. Return an empty questions array if this chunk contains no eligible questions.'.
                "\n\nPDF text:\n".$chunk
            );

            foreach ($response as $question) {
                $normalized = $this->normalizeQuestion($question);
                $hash = QuestionTextNormalizer::hash($normalized['question_text']);

                if ($hash !== null) {
                    $questions[$hash] = $normalized;
                }
            }
        }

        return array_values($questions);
    }

    /**
     * @return array<int, array{question_text: string, option_a: string, option_b: string, option_c: string, option_d: string, correct_option: string, explanation: string}>
     */
    public function generateQuestions(
        string $topic,
        string $difficulty,
        int $count,
        string $language,
        ?string $chapter = null,
    ): array
    {
        $topic = trim($topic);
        $chapter = trim($chapter ?? '');

        if ($topic === '') {
            throw new RuntimeException('Enter a topic or syllabus before generating questions.');
        }

        if ($count < 1 || $count > 50) {
            throw new RuntimeException('Question count must be between 1 and 50.');
        }

        if (! in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            throw new RuntimeException('Choose a valid question difficulty.');
        }

        if (! in_array($language, ['English', 'Hindi', 'Bilingual'], true)) {
            throw new RuntimeException('Choose a valid question language.');
        }

        $questions = [];

        foreach (array_chunk(range(1, $count), 10) as $batch) {
            $batchCount = count($batch);
            $response = $this->prompt(
                "Generate exactly {$batchCount} original, competitive-exam-level MCQs about the following topic or syllabus: {$topic}\n".
                ($chapter !== '' ? "Chapter context: {$chapter}. Keep the questions relevant to this chapter.\n" : '').
                "Difficulty: {$difficulty}.\n".
                "Language: {$language}. For Bilingual, write each question and option in both Hindi and English.\n".
                'Every question must have four plausible options and exactly one correct option. Include a concise explanation. Use $...$ for all mathematical or scientific equations. Do not repeat questions. Return exactly the requested number of questions.'
            );

            if (count($response) !== $batchCount) {
                throw new RuntimeException('The AI returned '.count($response)." of {$batchCount} requested questions. Please try again.");
            }

            foreach ($response as $question) {
                $questions[] = $this->normalizeQuestion($question);
            }
        }

        return $questions;
    }

    /**
     * @return array<int, string>
     */
    private function splitIntoChunks(string $text): array
    {
        $text = trim($text);
        $chunks = [];

        while ($text !== '') {
            if (mb_strlen($text, 'UTF-8') <= self::CHUNK_SIZE) {
                $chunks[] = $text;

                break;
            }

            $candidate = mb_substr($text, 0, self::CHUNK_SIZE, 'UTF-8');
            $breakAt = mb_strrpos($candidate, "\n", 0, 'UTF-8');

            if ($breakAt === false || $breakAt < self::CHUNK_SIZE / 2) {
                $breakAt = mb_strrpos($candidate, ' ', 0, 'UTF-8');
            }

            $length = $breakAt === false || $breakAt < self::CHUNK_SIZE / 2
                ? self::CHUNK_SIZE
                : $breakAt;

            $chunk = trim(mb_substr($text, 0, $length, 'UTF-8'));

            if ($chunk === '') {
                $length = self::CHUNK_SIZE;
                $chunk = trim(mb_substr($text, 0, $length, 'UTF-8'));
            }

            $chunks[] = $chunk;
            $text = trim(mb_substr(
                $text,
                max(1, $length - self::CHUNK_OVERLAP),
                null,
                'UTF-8',
            ));
        }

        return $chunks;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function prompt(string $prompt): array
    {
        $agent = QuestionGenerationAgent::make();
        $response = $agent->prompt(
            $prompt,
            provider: Lab::Gemini,
            model: config('question_ai.model'),
            timeout: 180,
        );

        if (! $response instanceof StructuredAgentResponse) {
            throw new RuntimeException('The AI provider did not return structured question data.');
        }

        $questions = $response->structured['questions'] ?? null;

        if (! is_array($questions)) {
            throw new RuntimeException('The AI response did not contain a valid questions array.');
        }

        return $questions;
    }

    /**
     * @param  array<string, mixed>  $question
     * @return array{question_text: string, option_a: string, option_b: string, option_c: string, option_d: string, correct_option: string, explanation: string}
     */
    private function normalizeQuestion(array $question): array
    {
        $normalized = [];

        foreach (['question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'explanation'] as $field) {
            if (! isset($question[$field]) || ! is_string($question[$field])) {
                throw new RuntimeException("The AI response contains an invalid {$field} field.");
            }

            $normalized[$field] = trim($question[$field]);
        }

        $correctOption = strtoupper((string) ($question['correct_option'] ?? ''));

        if (! in_array($correctOption, ['A', 'B', 'C', 'D'], true)) {
            throw new RuntimeException('The AI response contains an invalid correct_option value.');
        }

        if ($normalized['question_text'] === '' || in_array('', [
            $normalized['option_a'],
            $normalized['option_b'],
            $normalized['option_c'],
            $normalized['option_d'],
        ], true)) {
            throw new RuntimeException('The AI response contains a question with missing text or options.');
        }

        $normalized['correct_option'] = $correctOption;

        return $normalized;
    }
}
