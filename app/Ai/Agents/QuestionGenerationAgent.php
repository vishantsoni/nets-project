<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class QuestionGenerationAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return 'Return only multiple-choice questions as structured data. Each question must have question_text, option_a, option_b, option_c, option_d, correct_option (A, B, C, or D), and explanation. Do not invent facts or answers. Preserve the source language when extracting questions. Use $...$ for inline mathematical and scientific equations. Return only fields defined by the schema.';
    }

    public function schema(JsonSchema $schema): array
    {
        $question = $schema->object([
            'question_text' => $schema->string()->required(),
            'option_a' => $schema->string()->required(),
            'option_b' => $schema->string()->required(),
            'option_c' => $schema->string()->required(),
            'option_d' => $schema->string()->required(),
            'correct_option' => $schema->string()->enum(['A', 'B', 'C', 'D'])->required(),
            'explanation' => $schema->string()->required(),
        ])->withoutAdditionalProperties();

        return [
            'questions' => $schema->array()
                ->items($question)
                ->required(),
        ];
    }
}
