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
        // Load through the exam_questions pivot (ExamQuestion) so we can read the
        // per-exam marks/sort_order, and so section_id (nullable) does not
        // accidentally filter questions out of the student's view.
        return $this->exam->examQuestions()
            ->with([
                "question.options",
                "question.subject",
                "question.topic.chapter.subject",
                "question.topics.chapter.subject",
                "section",
            ])
            ->orderByRaw("section_id IS NULL")
            ->orderBy("section_id")
            ->orderBy("sort_order")
            ->get()
            ->map(function ($examQuestion) {
                $q = $examQuestion->question;
                $topics = collect([$q->topic])
                    ->filter()
                    ->merge($q->topics)
                    ->unique("id")
                    ->values();
                $subjectNames = collect([$q->subject?->name])
                    ->merge($topics->map(fn ($topic) => $topic->chapter?->subject?->name))
                    ->filter()
                    ->unique()
                    ->implode(", ");

                return [
                    "id" => $q->id,
                    "question_text" => $q->question_text,
                    "type" => $q->question_type,
                    "options" => $q->options,
                    "marks" => $examQuestion->marks,
                    "section_id" => $examQuestion->section_id,
                    "section" => $examQuestion->section?->title,
                    "subject" => $subjectNames,
                    "chapter" => $topics->map(fn ($topic) => $topic->chapter?->name)
                        ->filter()
                        ->unique()
                        ->implode(", "),
                    "topic" => $topics->pluck("name")->filter()->unique()->implode(", "),
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
