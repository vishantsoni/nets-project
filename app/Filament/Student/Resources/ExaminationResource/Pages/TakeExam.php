<?php
namespace App\Filament\Student\Resources\ExaminationResource\Pages;

use App\Filament\Student\Resources\ExaminationResource;
use App\Models\ExamAttempt;
use App\Models\Examination;
use App\Models\StudentAnswer;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;

class TakeExam extends Page
{
    protected static string $resource = ExaminationResource::class;
    protected static string $view = "filament.student.exams.take-exam";
    protected static ?string $navigationLabel = "Take Exam";
    protected static ?string $title = "Take Examination";

    public Examination $exam;
    public ExamAttempt $attempt;
    public $attemptTimeLeft = 0;

    public function mount(Examination $record): void
    {
        $this->exam = $record;

        $existingAttempt = ExamAttempt::where("user_id", auth()->id())
            ->where("examination_id", $record->id)
            ->where("status", "in_progress")
            ->first();

        if ($existingAttempt) {
            $this->attempt = $existingAttempt;
        } else {
            $this->attempt = ExamAttempt::create([
                "user_id" => auth()->id(),
                "examination_id" => $record->id,
                "attempt_number" => (ExamAttempt::where("user_id", auth()->id())
                    ->where("examination_id", $record->id)->count()) + 1,
                "status" => "in_progress",
                "start_time" => now(),
            ]);
        }

        $this->attemptTimeLeft = $record->duration * 60;
    }

    public function getQuestions(): array
    {
        return $this->exam->questions()->with("options")->get()->map(function ($q) {
            return [
                "id" => $q->id,
                "question_text" => $q->question_text,
                "type" => $q->type,
                "options" => $q->options,
                "marks" => $q->marks,
            ];
        })->toArray();
    }

    public function getAttemptId(): int
    {
        return $this->attempt->id;
    }

    public function getExamDuration(): int
    {
        return $this->exam->duration;
    }

    public function isShuffleQuestions(): bool
    {
        return $this->exam->shuffle_questions;
    }

    public function isShuffleOptions(): bool
    {
        return $this->exam->shuffle_options;
    }
}
