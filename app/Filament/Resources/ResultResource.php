<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ResultResource\Pages;
use App\Models\Result;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResultResource extends Resource
{
    protected static ?string $model = Result::class;
    protected static ?string $navigationIcon = "heroicon-o-chart-bar";
    protected static ?string $navigationGroup = "Examinations";
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Result")
                ->schema([
                    Forms\Components\Select::make("attempt_id")->required()->relationship("attempt", "uuid"),
                    Forms\Components\TextInput::make("total_questions")->integer(),
                    Forms\Components\TextInput::make("attempted_questions")->integer(),
                    Forms\Components\TextInput::make("correct_answers")->integer(),
                    Forms\Components\TextInput::make("incorrect_answers")->integer(),
                    Forms\Components\TextInput::make("unanswered_questions")->integer(),
                    Forms\Components\TextInput::make("total_marks")->numeric(),
                    Forms\Components\TextInput::make("obtained_marks")->numeric(),
                    Forms\Components\TextInput::make("percentage")->numeric(),
                    Forms\Components\TextInput::make("time_taken")->integer()->label("Time (seconds)"),
                    Forms\Components\TextInput::make("rank_position")->integer(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("attempt.user.name")->label("Student"),
            Tables\Columns\TextColumn::make("attempt.examination.title")->label("Exam")->limit(30),
            Tables\Columns\TextColumn::make("obtained_marks")->label("Obtained"),
            Tables\Columns\TextColumn::make("total_marks")->label("Total"),
            Tables\Columns\TextColumn::make("percentage")->label("%"),
            Tables\Columns\TextColumn::make("correct_answers")->label("Correct"),
            Tables\Columns\TextColumn::make("incorrect_answers")->label("Incorrect"),
            Tables\Columns\TextColumn::make("created_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->filters([
              Tables\Filters\SelectFilter::make("attempt.user_id")->relationship("attempt.user", "name")->label("Student"),
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
            "index" => Pages\ListResults::route("/"),
            "create" => Pages\CreateResult::route("/create"),
            "view" => Pages\ViewResult::route("/{record}"),
            "edit" => Pages\EditResult::route("/{record}/edit"),
        ];
    }
}
