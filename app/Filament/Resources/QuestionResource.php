<?php
namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;
    protected static ?string $navigationIcon = "heroicon-o-question-mark-circle";
    protected static ?string $navigationGroup = "Question Bank";
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Question Details")
                ->schema([
                    Forms\Components\Textarea::make("question_text")->required()->rows(3)->label("Question"),
                    Forms\Components\Select::make("question_type")
                        ->required()
                        ->options(["mcq" => "MCQ", "integer" => "Integer", "true_false" => "True/False", "fill_blank" => "Fill in the Blank", "essay" => "Essay"])
                        ->default("mcq"),
                    Forms\Components\Select::make("subject_id")->required()->relationship("subject", "name")->searchable(),
                    Forms\Components\Select::make("topic_id")->relationship("topic", "name")->searchable(),
                    Forms\Components\Select::make("difficulty")
                        ->options(["easy" => "Easy", "medium" => "Medium", "hard" => "Hard"])->default("medium"),
                    Forms\Components\TextInput::make("marks")->integer()->default(1),
                    Forms\Components\TextInput::make("negative_marks")->integer()->default(0),
                    Forms\Components\Textarea::make("correct_answer_text")->rows(2)->label("Correct Answer (for non-MCQ)"),
                    Forms\Components\Select::make("status")
                        ->options(["draft" => "Draft", "published" => "Published", "archived" => "Archived"])->default("draft"),
                    Forms\Components\Toggle::make("is_ai_generated")->default(false),
                ])->columns(2),
            Forms\Components\Section::make("Explanation")
                ->schema([Forms\Components\Textarea::make("explanation")->rows(3)]),
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
            Tables\Columns\TextColumn::make("marks")->label("Marks"),
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
        return [RelationManagers\QuestionOptionRelationManager::class];
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
