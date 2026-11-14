<?php

namespace Tests\Unit;

use App\Ai\Agents\QuestionGenerationAgent;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use PHPUnit\Framework\TestCase;

class QuestionGenerationAgentTest extends TestCase
{
    public function test_it_defines_strict_structured_mcq_output(): void
    {
        $schema = (new QuestionGenerationAgent)->schema(new JsonSchemaTypeFactory);
        $questionSchema = $schema['questions']->toArray();
        $itemSchema = $questionSchema['items'];

        $this->assertSame('array', $questionSchema['type']);
        $this->assertSame('object', $itemSchema['type']);
        $this->assertFalse($itemSchema['additionalProperties']);
        $this->assertSame([
            'question_text',
            'option_a',
            'option_b',
            'option_c',
            'option_d',
            'correct_option',
            'explanation',
        ], array_keys($itemSchema['properties']));
        $this->assertSame(['A', 'B', 'C', 'D'], $itemSchema['properties']['correct_option']['enum']);
    }
}
