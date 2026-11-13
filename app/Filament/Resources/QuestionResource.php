<?php
namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\ScopesToInstitute;
use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\Topic;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionResource extends Resource
{
    use ScopesToInstitute;

    protected static ?string $model = Question::class;
    protected static ?string $navigationIcon = "heroicon-o-question-mark-circle";
    protected static ?string $navigationGroup = "Question Bank";
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Question Details")
                ->schema([
                    Forms\Components\Textarea::make("question_text")
                        ->required()
                        ->rows(3)
                        ->label("Question")
                        ->extraInputAttributes(["data-math-input" => true]),
                    Forms\Components\Select::make("question_type")
                        ->required()
                        ->options(["mcq" => "MCQ", "integer" => "Integer", "true_false" => "True/False", "fill_blank" => "Fill in the Blank", "essay" => "Essay"])
                        ->default("mcq")
                        ->live(),
                    Forms\Components\Select::make("subject_id")
                        ->required()
                        ->relationship("subject", "name")
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (Set $set): void {
                            $set("chapter_id", null);
                            $set("topic_id", null);
                        }),
                    Forms\Components\Select::make("chapter_id")
                        ->label("Chapter")
                        ->options(fn (Get $get): array => filled($get("subject_id"))
                            ? Chapter::query()
                                ->forInstitute(auth()->user())
                                ->where("subject_id", $get("subject_id"))
                                ->orderBy("name")
                                ->pluck("name", "id")
                                ->all()
                            : [])
                        ->afterStateHydrated(fn (Forms\Components\Select $component, ?Question $record) => $component->state(
                            $record?->topic?->chapter_id
                        ))
                        ->dehydrated(false)
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set("topic_id", null))
                        ->disabled(fn (Get $get): bool => blank($get("subject_id")))
                        ->searchable(),
                    Forms\Components\Select::make("topic_id")
                        ->relationship(
                            "topic",
                            "name",
                            modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                ->forInstitute(auth()->user())
                                ->where("chapter_id", $get("chapter_id"))
                        )
                        ->disabled(fn (Get $get): bool => blank($get("chapter_id")))
                        ->searchable(),
                    Forms\Components\Select::make("difficulty")
                        ->options(["easy" => "Easy", "medium" => "Medium", "hard" => "Hard"])->default("medium"),
                    Forms\Components\Textarea::make("correct_answer_text")
                        ->rows(2)
                        ->label("Correct Answer (for non-MCQ)")
                        ->extraInputAttributes(["data-math-input" => true]),
                    Forms\Components\Select::make("status")
                        ->options(["draft" => "Draft", "published" => "Published", "archived" => "Archived"])->default("draft"),
                    Forms\Components\Toggle::make("is_ai_generated")->default(false),
                ])->columns(2),
            Forms\Components\Section::make("Answer Options")
                ->description("Add the answer choices and mark the correct one.")
                ->visible(fn (Get $get): bool => $get("question_type") === "mcq")
                ->schema([
                    Forms\Components\Repeater::make("options")
                        ->relationship()
                        ->label("Options")
                        ->defaultItems(4)
                        ->minItems(2)
                        ->addActionLabel("Add option")
                        ->orderColumn("sort_order")
                        ->schema([
                            Forms\Components\TextInput::make("option_value")
                                ->label("Option label")
                                ->placeholder("A")
                                ->maxLength(255)
                                ->extraInputAttributes(["data-option-label" => true]),
                            Forms\Components\Toggle::make("is_correct")
                                ->label("Correct answer")
                                ->default(false),
                            Forms\Components\Textarea::make("option_text")
                                ->label("Option text")
                                ->required()
                                ->rows(2)
                                ->extraInputAttributes(["data-math-input" => true])
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make("Explanation")
                ->schema([
                    Forms\Components\Textarea::make("explanation")
                        ->rows(3)
                        ->extraInputAttributes(["data-math-input" => true]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("question_text")->limit(50)->searchable(),
            Tables\Columns\BadgeColumn::make("question_type")
                ->colors(["primary" => "mcq", "warning" => "integer", "success" => "true_false", "info" => "fill_blank", "secondary" => "essay"]),
            Tables\Columns\TextColumn::make("subject.name")->label("Subject"),
            Tables\Columns\TextColumn::make("difficulty"),
            Tables\Columns\BadgeColumn::make("status")
                ->colors(["gray" => "draft", "warning" => "published", "success" => "archived"]),
        ])->defaultSort("created_at", "desc")
          ->filters([
              Tables\Filters\SelectFilter::make("question_type")
                  ->options(["mcq" => "MCQ", "integer" => "Integer", "true_false" => "True/False", "fill_blank" => "Fill in the Blank", "essay" => "Essay"]),
              Tables\Filters\SelectFilter::make("subject_id")->relationship("subject", "name"),
              Tables\Filters\SelectFilter::make("status")
                  ->options(["draft" => "Draft", "published" => "Published", "archived" => "Archived"]),
          ])
          ->actions([
              Tables\Actions\ViewAction::make(),
              Tables\Actions\EditAction::make(),
              Tables\Actions\DeleteAction::make(),
          ])
          ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListQuestions::route("/"),
            "create" => Pages\CreateQuestion::route("/create"),
            "view" => Pages\ViewQuestion::route("/{record}"),
            "edit" => Pages\EditQuestion::route("/{record}/edit"),
        ];
    }
}
