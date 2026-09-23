<?php
namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\ResultResource\Pages;
use App\Models\Result;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ResultResource extends Resource
{
    protected static ?string $model = Result::class;

    protected static ?string $navigationIcon = "heroicon-o-chart-bar";
    protected static ?string $navigationLabel = "My Results";
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Result Details")
                ->schema([
                    Forms\Components\TextInput::make("attempt.examination.title")->readOnly()->label("Examination"),
                    Forms\Components\TextInput::make("total_questions")->integer()->readOnly(),
                    Forms\Components\TextInput::make("attempted_questions")->integer()->readOnly(),
                    Forms\Components\TextInput::make("correct_answers")->integer()->readOnly()->label("Correct"),
                    Forms\Components\TextInput::make("incorrect_answers")->integer()->readOnly()->label("Incorrect"),
                    Forms\Components\TextInput::make("unanswered_questions")->integer()->readOnly()->label("Unanswered"),
                    Forms\Components\TextInput::make("total_marks")->numeric()->readOnly(),
                    Forms\Components\TextInput::make("obtained_marks")->numeric()->readOnly(),
                    Forms\Components\TextInput::make("percentage")->readOnly(),
                    Forms\Components\TextInput::make("time_taken")->integer()->readOnly()->label("Time (seconds)"),
                    Forms\Components\Toggle::make("is_passed")->disabled(),
                    Forms\Components\TextInput::make("rank_position")->integer()->readOnly(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("attempt.examination.title")->label("Examination")->limit(30),
            Tables\Columns\TextColumn::make("obtained_marks")->label("Obtained"),
            Tables\Columns\TextColumn::make("total_marks")->label("Total"),
            Tables\Columns\TextColumn::make("percentage")->label("%"),
            Tables\Columns\TextColumn::make("correct_answers")->label("Correct"),
            Tables\Columns\BadgeColumn::make("is_passed")
                ->label("Status")
                ->colors(["success" => true, "danger" => false])
                ->trueLabel("PASSED")->falseLabel("FAILED"),
            Tables\Columns\TextColumn::make("created_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->actions([Tables\Actions\ViewAction::make()])
          ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListResults::route("/"),
            "view" => Pages\ViewResult::route("/{record}"),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas("attempt", function ($q) {
            $q->where("user_id", auth()->id());
        });
    }
}
