<?php

namespace App\Services;

use App\Models\Examination;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class QuestionImportService
{
    /**
     * @param  array<int|string, array<string, mixed>>  $questions
     * @return array{saved: int, duplicates: int, processed: array<int|string, int>}
     *
     * @throws ValidationException
     */
    public function import(
        array $questions,
        int $subjectId,
        ?int $topicId,
        ?int $examinationId,
        string $difficulty,
        bool $isAiGenerated,
    ): array {
        if (! in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            throw ValidationException::withMessages([
                'difficulty' => 'Choose a valid question difficulty.',
            ]);
        }

        $validated = Validator::make(['questions' => $questions], [
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.option_a' => ['required', 'string'],
            'questions.*.option_b' => ['required', 'string'],
            'questions.*.option_c' => ['required', 'string'],
            'questions.*.option_d' => ['required', 'string'],
            'questions.*.correct_option' => ['required', 'in:A,B,C,D'],
            'questions.*.explanation' => ['nullable', 'string'],
        ])->validate();

        $user = Auth::user();
        $subject = Subject::query()->forInstitute($user)->findOrFail($subjectId);

        if ($topicId !== null) {
            Topic::query()
                ->forInstitute($user)
                ->whereHas('chapter', fn ($query) => $query->where('subject_id', $subjectId))
                ->findOrFail($topicId);
        }

        $examination = null;

        if ($examinationId !== null) {
            $examination = Examination::query()
                ->forInstitute($user)
                ->where('subject_id', $subjectId)
                ->findOrFail($examinationId);
        }

        $hashes = [];

        foreach ($validated['questions'] as $index => $question) {
            $hash = QuestionTextNormalizer::hash($question['question_text']);

            if ($hash === null) {
                throw ValidationException::withMessages([
                    "questions.{$index}.question_text" => 'The question text must contain letters or numbers.',
                ]);
            }

            $hashes[$index] = $hash;
        }

        $instituteId = $subject->institute_id ?? $user?->institute_id;

        return DB::transaction(function () use (
            $validated,
            $hashes,
            $subject,
            $topicId,
            $examination,
            $instituteId,
            $difficulty,
            $isAiGenerated,
        ): array {
            $existingQuestions = Question::query()
                ->whereIn('question_hash', array_values(array_unique($hashes)))
                ->when(
                    $instituteId === null,
                    fn ($query) => $query->whereNull('institute_id'),
                    fn ($query) => $query->where('institute_id', $instituteId),
                )
                ->lockForUpdate()
                ->get(['id', 'question_hash'])
                ->mapWithKeys(fn (Question $question): array => [$question->question_hash => $question->id])
                ->all();
            $saved = 0;
            $duplicates = 0;
            $processed = [];
            $sortOrder = $examination
                ? ((int) ($examination->examQuestions()->max('sort_order') ?? -1)) + 1
                : 0;

            foreach ($validated['questions'] as $index => $data) {
                $hash = $hashes[$index];

                if (isset($existingQuestions[$hash])) {
                    $duplicates++;
                    $processed[$index] = $existingQuestions[$hash];

                    continue;
                }

                $question = Question::create([
                    'question_text' => trim($data['question_text']),
                    'question_type' => 'mcq',
                    'subject_id' => $subject->getKey(),
                    'topic_id' => $topicId,
                    'difficulty' => $difficulty,
                    'explanation' => filled($data['explanation'] ?? null)
                        ? trim($data['explanation'])
                        : null,
                    'status' => $examination ? 'published' : 'draft',
                    'is_ai_generated' => $isAiGenerated,
                    'institute_id' => $instituteId,
                    'created_by' => Auth::id(),
                ]);

                foreach (['A', 'B', 'C', 'D'] as $sortIndex => $option) {
                    $question->options()->create([
                        'option_value' => $option,
                        'option_text' => trim($data['option_'.strtolower($option)]),
                        'is_correct' => $data['correct_option'] === $option,
                        'sort_order' => $sortIndex,
                    ]);
                }

                if ($examination) {
                    $examination->examQuestions()->create([
                        'question_id' => $question->getKey(),
                        'sort_order' => $sortOrder++,
                        'marks' => 1,
                    ]);
                }

                $existingQuestions[$hash] = $question->getKey();
                $processed[$index] = $question->getKey();
                $saved++;
            }

            return compact('saved', 'duplicates', 'processed');
        });
    }
}
