<?php

namespace App\Filament\Resources\ExaminationResource\RelationManagers;

use App\Models\Chapter;
use App\Models\ExamQuestion;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamQuestionRelationManager extends RelationManager
{
    protected static string $relationship = "examQuestions";
    protected static ?string $recordLabel = "Question";

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Question Assignment")
                ->schema([
                    Forms\Components\Select::make("filter_subject_id")
                        ->label("Filter by subject")
                        ->options(fn (): array => Subject::query()
                            ->forInstitute(auth()->user())
                            ->orderBy("name")
                            ->pluck("name", "id")
                            ->all())
                        ->required()
                        ->searchable()
                        ->live()
                        ->dehydrated(false)
                        ->afterStateHydrated(fn (Forms\Components\Select $component, ?ExamQuestion $record) => $component->state(
                            $record?->question?->subject_id
                        ))
                        ->afterStateUpdated(function (Set $set): void {
                            $set("filter_chapter_id", null);
                            $set("filter_topic_id", null);
                            $set("question_id", null);
                        }),
                    Forms\Components\Select::make("filter_chapter_id")
                        ->label("Filter by chapter")
                        ->options(fn (Get $get): array => filled($get("filter_subject_id"))
                            ? Chapter::query()
                                ->forInstitute(auth()->user())
                                ->where("subject_id", $get("filter_subject_id"))
                                ->orderBy("name")
                                ->pluck("name", "id")
                                ->all()
                            : [])
                        ->searchable()
                        ->live()
                        ->dehydrated(false)
                        ->disabled(fn (Get $get): bool => blank($get("filter_subject_id")))
                        ->afterStateHydrated(fn (Forms\Components\Select $component, ?ExamQuestion $record) => $component->state(
                            $record?->question?->topic?->chapter_id
                        ))
                        ->afterStateUpdated(function (Set $set): void {
                            $set("filter_topic_id", null);
                            $set("question_id", null);
                        }),
                    Forms\Components\Select::make("filter_topic_id")
                        ->label("Filter by topic")
                        ->options(fn (Get $get): array => filled($get("filter_chapter_id"))
                            ? Topic::query()
                                ->forInstitute(auth()->user())
                                ->where("chapter_id", $get("filter_chapter_id"))
                                ->orderBy("name")
                                ->pluck("name", "id")
                                ->all()
                            : [])
                        ->searchable()
                        ->live()
                        ->dehydrated(false)
                        ->disabled(fn (Get $get): bool => blank($get("filter_chapter_id")))
                        ->afterStateHydrated(fn (Forms\Components\Select $component, ?ExamQuestion $record) => $component->state(
                            $record?->question?->topic_id
                        ))
                        ->afterStateUpdated(fn (Set $set) => $set("question_id", null)),
                    Forms\Components\Select::make("question_id")
                        ->label("Question")
                        ->relationship(
                            "question",
                            "question_text",
                            fn (Builder $query, Get $get): Builder => $this->applyQuestionFilters($query, $get)
                        )
                        ->searchable()
                        ->required()
                        ->disabled(fn (Get $get): bool => blank($get("filter_subject_id")))
                        ->helperText("Select a subject first, then optionally narrow by chapter and topic.")
                        ->columnSpanFull(),
                    Forms\Components\Select::make("section_id")
                        ->label("Section (optional)")
                        ->options(fn () => $this->getOwnerRecord()
                            ->sections()
                            ->pluck("title", "id"))
                        ->nullable()
                        ->searchable(),
                    Forms\Components\TextInput::make("marks")
                        ->integer()
                        ->default(1)
                        ->required(),
                    Forms\Components\TextInput::make("sort_order")
                        ->integer()
                        ->default(0)
                        ->required(),
                ])->columns(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("sort_order")
                ->label("Order")
                ->sortable(),
            Tables\Columns\TextColumn::make("question.question_text")
                ->label("Question")
                ->limit(60)
                ->searchable(),
            Tables\Columns\BadgeColumn::make("question.question_type")
                ->label("Type")
                ->colors([
                    "primary" => "mcq",
                    "warning" => "integer",
                    "success" => "true_false",
                    "info" => "fill_blank",
                    "secondary" => "essay",
                ]),
            Tables\Columns\TextColumn::make("section.title")
                ->label("Section")
                ->placeholder("None"),
            Tables\Columns\TextColumn::make("marks")->label("Marks"),
        ])->defaultSort("sort_order")
          ->headerActions([
              Tables\Actions\CreateAction::make(),
              Tables\Actions\Action::make("addMultipleQuestions")
                  ->label("Add multiple questions")
                  ->icon("heroicon-o-plus")
                  ->modalHeading("Add multiple questions")
                  ->modalSubmitActionLabel("Add questions")
                  ->form([
                      Forms\Components\Select::make("filter_subject_id")
                          ->label("Filter by subject")
                          ->options(fn (): array => Subject::query()
                              ->forInstitute(auth()->user())
                              ->orderBy("name")
                              ->pluck("name", "id")
                              ->all())
                          ->required()
                          ->searchable()
                          ->live()
                          ->afterStateUpdated(function (Set $set): void {
                              $set("filter_chapter_id", null);
                              $set("filter_topic_id", null);
                              $set("question_ids", []);
                          }),
                      Forms\Components\Select::make("filter_chapter_id")
                          ->label("Filter by chapter")
                          ->options(fn (Get $get): array => filled($get("filter_subject_id"))
                              ? Chapter::query()
                                  ->forInstitute(auth()->user())
                                  ->where("subject_id", $get("filter_subject_id"))
                                  ->orderBy("name")
                                  ->pluck("name", "id")
                                  ->all()
                              : [])
                          ->searchable()
                          ->live()
                          ->disabled(fn (Get $get): bool => blank($get("filter_subject_id")))
                          ->afterStateUpdated(function (Set $set): void {
                              $set("filter_topic_id", null);
                              $set("question_ids", []);
                          }),
                      Forms\Components\Select::make("filter_topic_id")
                          ->label("Filter by topic")
                          ->options(fn (Get $get): array => filled($get("filter_chapter_id"))
                              ? Topic::query()
                                  ->forInstitute(auth()->user())
                                  ->where("chapter_id", $get("filter_chapter_id"))
                                  ->orderBy("name")
                                  ->pluck("name", "id")
                                  ->all()
                              : [])
                          ->searchable()
                          ->live()
                          ->disabled(fn (Get $get): bool => blank($get("filter_chapter_id")))
                          ->afterStateUpdated(fn (Set $set) => $set("question_ids", null)),
                      Forms\Components\CheckboxList::make("question_ids")
                          ->label("Questions")
                          ->required()
                          ->options(fn (Get $get): array => blank($get("filter_subject_id"))
                              ? []
                              : $this->applyQuestionFilters(Question::query(), $get)
                                  ->whereNotIn(
                                      "id",
                                      $this->getOwnerRecord()->examQuestions()->select("question_id")
                                  )
                                  ->orderBy("id")
                                  ->get(["id", "question_text"])
                                  ->mapWithKeys(fn (Question $question): array => [
                                      $question->id => "Q{$question->id} - {$question->question_text}",
                                  ])
                                  ->all())
                          ->searchable()
                          ->bulkToggleable()
                          ->columns(1)
                          ->disabled(fn (Get $get): bool => blank($get("filter_subject_id")))
                          ->helperText("Choose questions with checkboxes. Their order will continue from the last question in this exam.")
                          ->columnSpanFull(),
                      Forms\Components\Select::make("section_id")
                          ->label("Section (optional)")
                          ->options(fn () => $this->getOwnerRecord()
                              ->sections()
                              ->pluck("title", "id"))
                          ->nullable()
                          ->searchable(),
                      Forms\Components\TextInput::make("marks")
                          ->integer()
                          ->default(1)
                          ->required()
                          ->helperText("The same marks value will be used for every selected question."),
                  ])
                  ->action(function (array $data): void {
                      $questionIds = collect($data["question_ids"])->map(fn ($id): int => (int) $id)->unique()->values();
                      $questions = Question::query()
                          ->where("status", "published")
                          ->forInstitute(auth()->user())
                          ->whereIn("id", $questionIds)
                          ->get(["id"]);

                      if ($questions->count() !== $questionIds->count()) {
                          throw ValidationException::withMessages([
                              "question_ids" => "Select only published questions available to your institute.",
                          ]);
                      }

                      $addedCount = DB::transaction(function () use ($data, $questionIds): int {
                          $exam = $this->getOwnerRecord();
                          $existingIds = $exam->examQuestions()
                              ->whereIn("question_id", $questionIds)
                              ->pluck("question_id");
                          $newQuestionIds = $questionIds->diff($existingIds);
                          $sortOrder = ((int) ($exam->examQuestions()->max("sort_order") ?? -1)) + 1;

                          foreach ($newQuestionIds as $questionId) {
                              $exam->examQuestions()->create([
                                  "question_id" => $questionId,
                                  "section_id" => $data["section_id"] ?? null,
                                  "marks" => $data["marks"],
                                  "sort_order" => $sortOrder++,
                              ]);
                          }

                          return $newQuestionIds->count();
                      });

                      $skippedCount = $questionIds->count() - $addedCount;
                      $notification = Notification::make()
                          ->title($addedCount > 0 ? "Questions added" : "No new questions added")
                          ->body($skippedCount > 0
                              ? "{$addedCount} question(s) added; {$skippedCount} already assigned and skipped."
                              : "{$addedCount} question(s) added to this exam.");

                      if ($addedCount > 0) {
                          $notification->success();
                      } else {
                          $notification->warning();
                      }

                      $notification->send();
                  }),
          ])
          ->actions([
              Tables\Actions\EditAction::make(),
              Tables\Actions\DeleteAction::make(),
          ])
          ->reorderable("sort_order");
    }

    private function applyQuestionFilters(Builder $query, Get $get): Builder
    {
        $query->where("status", "published")
            ->forInstitute(auth()->user());

        if (blank($get("filter_subject_id"))) {
            return $query->whereRaw("1 = 0");
        }

        $query->where("subject_id", $get("filter_subject_id"));

        if (filled($get("filter_chapter_id"))) {
            $query->whereHas(
                "topic",
                fn (Builder $topicQuery) => $topicQuery->where("chapter_id", $get("filter_chapter_id"))
            );
        }

        if (filled($get("filter_topic_id"))) {
            $query->where("topic_id", $get("filter_topic_id"));
        }

        return $query;
    }
}