<?php
namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\ExamAttemptResource\Pages;
use App\Models\ExamAttempt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExamAttemptResource extends Resource
{
    protected static ?string $model = ExamAttempt::class;

    protected static ?string $navigationIcon = "heroicon-o-clipboard-document-list";
    protected static ?string $navigationLabel = "My Attempts";
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Attempt Details")
                ->schema([
                    Forms\Components\TextInput::make("uuid")->readOnly(),
                    Forms\Components\TextInput::make("examination.title")->readOnly()->label("Exam"),
                    Forms\Components\TextInput::make("attempt_number")->integer()->readOnly(),
                    Forms\Components\Select::make("status")
                        ->options(["in_progress" => "In Progress", "completed" => "Completed", "timed_out" => "Timed Out"])->disabled(),
                    Forms\Components\TextInput::make("total_marks")->numeric()->readOnly(),
                    Forms\Components\TextInput::make("obtained_marks")->numeric()->readOnly(),
                    Forms\Components\TextInput::make("percentage")->readOnly(),
                    Forms\Components\Toggle::make("is_passed")->disabled(),
                    Forms\Components\TextInput::make("time_taken")->integer()->readOnly()->label("Time Taken (seconds)"),
                    Forms\Components\DateTimePicker::make("started_at")->readOnly(),
                    Forms\Components\DateTimePicker::make("ended_at")->readOnly(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("uuid")->limit(8),
            Tables\Columns\TextColumn::make("examination.title")->label("Examination")->limit(30),
            Tables\Columns\TextColumn::make("attempt_number")->label("#"),
            Tables\Columns\BadgeColumn::make("status")
                ->colors(["warning" => "in_progress", "success" => "completed", "danger" => "timed_out"]),
            Tables\Columns\TextColumn::make("obtained_marks")->label("Marks"),
            Tables\Columns\TextColumn::make("percentage")->label("%"),
            Tables\Columns\TextColumn::make("started_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->filters([
              Tables\Filters\SelectFilter::make("examination_id")->relationship("examination", "title")->label("Examination"),
          ])
          ->actions([
              Tables\Actions\ViewAction::make(),
          ])
          ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListExamAttempts::route("/"),
            "view" => Pages\ViewExamAttempt::route("/{record}"),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where("user_id", auth()->id());
    }
}
