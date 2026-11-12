<?php

namespace App\Services\PdfExtraction;

class McqQuestionParser
{
    /**
     * Parse numbered multiple-choice questions with lettered options and
     * inline answers or a trailing answer key.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $content): array
    {
        $lines = preg_split('/\R/u', $content) ?: [];
        $blocks = [];
        $answerKey = [];
        $currentQuestion = null;
        $readingAnswerKey = false;

        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match('/^(?:answer\s*key|answers?)\s*:?\s*$/i', $line) === 1) {
                if ($currentQuestion !== null) {
                    $blocks[] = $currentQuestion;
                    $currentQuestion = null;
                }

                $readingAnswerKey = true;

                continue;
            }

            if ($readingAnswerKey) {
                preg_match_all(
                    '/(?:^|[\s,;])(\d{1,3})\s*[\.\)\-:]\s*\(?([A-D])\)?(?=$|[\s,;])/i',
                    $line,
                    $matches,
                    PREG_SET_ORDER,
                );

                foreach ($matches as $match) {
                    $answerKey[(int) $match[1]] = strtoupper($match[2]);
                }

                continue;
            }

            if (preg_match('/^(?:Q(?:uestion)?\s*)?(\d{1,3})\s*[\.\)]\s*(.*)$/i', $line, $match) === 1) {
                if ($currentQuestion !== null) {
                    $blocks[] = $currentQuestion;
                }

                $currentQuestion = [
                    'number' => (int) $match[1],
                    'lines' => $match[2] === '' ? [] : [$match[2]],
                ];

                continue;
            }

            if ($currentQuestion !== null && $line !== '') {
                $currentQuestion['lines'][] = $line;
            }
        }

        if ($currentQuestion !== null) {
            $blocks[] = $currentQuestion;
        }

        $questions = [];

        foreach ($blocks as $block) {
            $questionText = [];
            $options = [];
            $activeOption = null;
            $answer = $answerKey[$block['number']] ?? null;

            foreach ($block['lines'] as $line) {
                if (preg_match('/(?:correct\s+)?ans(?:wer)?\s*[:\.\-]\s*\(?([A-D])\)?/i', $line, $answerMatch) === 1) {
                    $answer = strtoupper($answerMatch[1]);
                    $line = trim(str_replace($answerMatch[0], '', $line));
                }

                if ($line === '') {
                    continue;
                }

                preg_match_all(
                    '/(?<!\S)(?:\(([A-D])\)|([A-D])[\.\)])\s*/i',
                    $line,
                    $optionMatches,
                    PREG_SET_ORDER | PREG_OFFSET_CAPTURE,
                );

                $firstOptionOffset = $optionMatches[0][0][1] ?? null;
                $shouldSplitOptions = $firstOptionOffset === 0
                    || count($optionMatches) > 1
                    || ($activeOption !== null && $optionMatches !== []);

                if ($shouldSplitOptions) {
                    if ($firstOptionOffset > 0) {
                        $prefix = trim(substr($line, 0, $firstOptionOffset));

                        if ($prefix !== '') {
                            if ($activeOption === null) {
                                $questionText[] = $prefix;
                            } else {
                                $options[$activeOption]['option_text'] = trim($options[$activeOption]['option_text'].' '.$prefix);
                            }
                        }
                    }

                    foreach ($optionMatches as $index => $optionMatch) {
                        $label = strtoupper($optionMatch[1][1] >= 0 ? $optionMatch[1][0] : $optionMatch[2][0]);
                        $optionTextStart = $optionMatch[0][1] + strlen($optionMatch[0][0]);
                        $optionTextEnd = $optionMatches[$index + 1][0][1] ?? strlen($line);
                        $options[] = [
                            'option_value' => $label,
                            'option_text' => trim(substr($line, $optionTextStart, $optionTextEnd - $optionTextStart)),
                            'is_correct' => false,
                        ];
                        $activeOption = array_key_last($options);
                    }

                    continue;
                }

                if ($activeOption === null) {
                    $questionText[] = $line;
                } else {
                    $options[$activeOption]['option_text'] = trim($options[$activeOption]['option_text'].' '.$line);
                }
            }

            if (count($options) < 2 || trim(implode(' ', $questionText)) === '') {
                continue;
            }

            foreach ($options as &$option) {
                $option['is_correct'] = $option['option_value'] === $answer;
            }
            unset($option);
            $hasOneCorrectAnswer = count(array_filter(
                $options,
                fn (array $option): bool => $option['is_correct'],
            )) === 1;

            $questions[] = [
                'question_text' => trim(implode(' ', $questionText)),
                'options' => $options,
                'explanation' => null,
                'selected' => $hasOneCorrectAnswer,
                'imported_question_id' => null,
            ];
        }

        return $questions;
    }
}
