<?php

namespace App\Http\Controllers;

use App\Filament\Student\Resources\ResultResource;
use App\Models\ExamAttempt;
use App\Models\Examination;
use App\Models\Question;
use App\Models\Result;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StudentExamController extends Controller
{
    public function saveAnswer(Request $request)
    {
        $request->validate([
            'attempt_id' => 'required|exists:exam_attempts,id',
            'question_id' => 'required|exists:questions,id',
            'answer' => 'nullable|numeric',
        ]);

        $attempt = ExamAttempt::findOrFail($request->attempt_id);

        if ($attempt->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $question = Question::findOrFail($request->question_id);

        $answerValue = $request->answer;
        if (is_bool($answerValue)) {
            $answerValue = $answerValue ? 'true' : 'false';
        }

        StudentAnswer::updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $question->id],
            ['answer_text' => $answerValue]
        );

        return response()->json(['success' => true]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'attempt_id' => 'required|exists:exam_attempts,id',
        ]);

        $attempt = ExamAttempt::findOrFail($request->attempt_id);

        if ($attempt->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $exam = $attempt->examination;
        $examQuestions = $exam->examQuestions()->with('question.options')->get();
        $totalMarks = 0;
        $obtainedMarks = 0;
        $correct = 0;
        $incorrect = 0;
        $unanswered = 0;

        $answers = StudentAnswer::where('attempt_id', $attempt->id)->get();

        foreach ($examQuestions as $examQuestion) {
            $question = $examQuestion->question;
            $marks = (float) $examQuestion->marks;
            $totalMarks += $marks;
            $answer = $answers->firstWhere('question_id', $question->id);

            if (! $answer || $answer->answer_text === null || $answer->answer_text === '') {
                $unanswered++;

                continue;
            }

            $isCorrect = $this->evaluateAnswer($question, $answer);

            $marksObtained = 0;
            if ($isCorrect) {
                $correct++;
                $marksObtained = $marks;
                $obtainedMarks += $marks;
            } else {
                $incorrect++;
                if ($exam->negative_marking) {
                    $deduction = $marks * ($exam->negative_marks_ratio ?? 0.25);
                    $marksObtained = -$deduction;
                    $obtainedMarks -= $deduction;
                }
            }

            $answer->update([
                'is_correct' => $isCorrect,
                'marks_obtained' => $marksObtained,
            ]);
        }

        $percentage = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;
        $isPassed = $percentage >= ($exam->passing_marks ?? 40);

        $attempt->update([
            'status' => 'completed',
            'end_time' => now(),
            'total_marks' => $totalMarks,
            'obtained_marks' => $obtainedMarks,
            'percentage' => $percentage,
            'is_passed' => $isPassed,
            'time_taken' => now()->diffInSeconds($attempt->start_time),
        ]);

        $result = Result::updateOrCreate(
            ['attempt_id' => $attempt->id],
            [
                'total_questions' => $examQuestions->count(),
                'attempted_questions' => $examQuestions->count() - $unanswered,
                'correct_answers' => $correct,
                'incorrect_answers' => $incorrect,
                'unanswered_questions' => $unanswered,
                'total_marks' => $totalMarks,
                'obtained_marks' => $obtainedMarks,
                'percentage' => $percentage,
                'is_passed' => $isPassed,
                'time_taken' => now()->diffInSeconds($attempt->start_time),
            ]
        );

        return response()->json([
            'success' => true,
            'redirect_url' => ResultResource::getUrl('view', ['record' => $result]),
        ]);
    }

    public function leaderboard(Examination $examination)
    {
        abort_unless(
            Result::query()
                ->whereHas('attempt', fn ($query) => $query
                    ->where('examination_id', $examination->id)
                    ->where('user_id', auth()->id())
                    ->where('status', 'completed'))
                ->exists(),
            403
        );

        $bestResultByStudent = Result::query()
            ->with('attempt.user')
            ->whereHas('attempt', fn ($query) => $query
                ->where('examination_id', $examination->id)
                ->where('status', 'completed'))
            ->get()
            ->filter(fn (Result $result) => $result->attempt?->user)
            ->groupBy(fn (Result $result) => $result->attempt->user_id)
            ->map(fn (Collection $results) => $results->sort(
                fn (Result $a, Result $b) => $this->compareResults($a, $b)
            )->first())
            ->values()
            ->sort(fn (Result $a, Result $b) => $this->compareResults($a, $b))
            ->values();

        $rank = 0;
        $previousMarks = null;
        $leaderboard = $bestResultByStudent->map(function (Result $result, int $index) use (&$rank, &$previousMarks) {
            $marks = (float) $result->obtained_marks;
            if ($previousMarks === null || $marks < $previousMarks) {
                $rank = $index + 1;
                $previousMarks = $marks;
            }

            return [
                'rank' => $rank,
                'name' => $result->attempt->user->name,
                'obtained_marks' => $result->obtained_marks,
                'total_marks' => $result->total_marks,
                'percentage' => $result->percentage,
                'time_taken' => $result->time_taken,
                'is_current_user' => $result->attempt->user_id === auth()->id(),
            ];
        });

        return view('filament.student.exams.leaderboard', [
            'exam' => $examination,
            'leaderboard' => $leaderboard,
        ]);
    }

    protected function compareResults(Result $a, Result $b): int
    {
        $marksComparison = (float) $b->obtained_marks <=> (float) $a->obtained_marks;
        if ($marksComparison !== 0) {
            return $marksComparison;
        }

        $timeComparison = (int) $a->time_taken <=> (int) $b->time_taken;
        if ($timeComparison !== 0) {
            return $timeComparison;
        }

        return $a->id <=> $b->id;
    }

    protected function evaluateAnswer($question, $answer): bool
    {
        if ($question->question_type === 'mcq') {
            $options = $question->options->where('is_correct', true);

            return $options->contains('id', $answer->answer_text);
        }

        if ($question->question_type === 'true_false') {
            $correct = $question->options->where('is_correct', true)->first();

            return strtolower($answer->answer_text) === strtolower($correct->option_text);
        }

        if ($question->question_type === 'integer' || $question->question_type === 'fill_blank') {
            return trim($answer->answer_text) === trim($question->correct_answer_text ?? '');
        }

        if ($question->question_type === 'essay') {
            return false;
        }

        return false;
    }
}
