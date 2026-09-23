<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\Question;
use App\Models\Result;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentExamController extends Controller
{
    public function saveAnswer(Request $request)
    {
        $request->validate([
            "attempt_id" => "required|exists:exam_attempts,id",
            "question_id" => "required|exists:questions,id",
            "answer" => "nullable|string",
        ]);

        $attempt = ExamAttempt::findOrFail($request->attempt_id);

        if ($attempt->user_id !== auth()->id()) {
            return response()->json(["error" => "Unauthorized"], 403);
        }

        $question = Question::findOrFail($request->question_id);

        $answerValue = $request->answer;
        if (is_bool($answerValue)) {
            $answerValue = $answerValue ? "true" : "false";
        }

        StudentAnswer::updateOrCreate(
            ["attempt_id" => $attempt->id, "question_id" => $question->id],
            ["answer_text" => $answerValue]
        );

        return response()->json(["success" => true]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            "attempt_id" => "required|exists:exam_attempts,id",
        ]);

        $attempt = ExamAttempt::findOrFail($request->attempt_id);

        if ($attempt->user_id !== auth()->id()) {
            return response()->json(["error" => "Unauthorized"], 403);
        }

        $exam = $attempt->examination;
        $questions = $exam->questions;
        $totalMarks = 0;
        $obtainedMarks = 0;
        $correct = 0;
        $incorrect = 0;
        $unanswered = 0;

        $answers = StudentAnswer::where("attempt_id", $attempt->id)->get();

        foreach ($questions as $question) {
            $totalMarks += $question->marks;
            $answer = $answers->firstWhere("question_id", $question->id);

            if (!$answer || $answer->answer_text === null || $answer->answer_text === "") {
                $unanswered++;
                continue;
            }

            $isCorrect = $this->evaluateAnswer($question, $answer);

            if ($isCorrect) {
                $correct++;
                $obtainedMarks += $question->marks;
            } else {
                $incorrect++;
                if ($exam->negative_marking) {
                    $obtainedMarks -= ($question->marks * ($exam->negative_marks_ratio ?? 0.25));
                }
            }
        }

        $percentage = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;
        $isPassed = $percentage >= ($exam->passing_marks ?? 40);

        $attempt->update([
            "status" => "completed",
            "end_time" => now(),
            "total_marks" => $totalMarks,
            "obtained_marks" => $obtainedMarks,
            "percentage" => $percentage,
            "is_passed" => $isPassed,
            "time_taken" => now()->diffInSeconds($attempt->start_time),
        ]);

        Result::updateOrCreate(
            ["attempt_id" => $attempt->id],
            [
                "total_questions" => $questions->count(),
                "attempted_questions" => $questions->count() - $unanswered,
                "correct_answers" => $correct,
                "incorrect_answers" => $incorrect,
                "unanswered_questions" => $unanswered,
                "total_marks" => $totalMarks,
                "obtained_marks" => $obtainedMarks,
                "percentage" => $percentage,
                "is_passed" => $isPassed,
                "time_taken" => now()->diffInSeconds($attempt->start_time),
            ]
        );

        return response()->json(["success" => true]);
    }

    protected function evaluateAnswer($question, $answer): bool
    {
        if ($question->question_type === "mcq") {
            $options = $question->options->where("is_correct", true);
            return $options->contains("id", $answer->answer_text);
        }

        if ($question->question_type === "true_false") {
            $correct = $question->options->where("is_correct", true)->first();
            return strtolower($answer->answer_text) === strtolower($correct->option_text);
        }

        if ($question->question_type === "integer" || $question->question_type === "fill_blank") {
            return trim($answer->answer_text) === trim($question->correct_answer_text ?? "");
        }

        if ($question->question_type === "essay") {
            return false;
        }

        return false;
    }
}
