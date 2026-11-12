<?php

namespace App\Filament\Resources\ExaminationResource\RelationManagers;

use App\Models\Chapter;
use App\Models\ExamQuestion;
use App\Models\Subject;
use App\Models\Topic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                            function (Builder $query, Get $get): Builder {
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
          ])
          ->actions([
              Tables\Actions\EditAction::make(),
              Tables\Actions\DeleteAction::make(),
          ])
          ->reorderable("sort_order");
    }
}