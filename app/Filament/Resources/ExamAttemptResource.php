<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ExamAttemptResource\Pages;
use App\Models\ExamAttempt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExamAttemptResource extends Resource
{
    protected static ?string $model = ExamAttempt::class;
    protected static ?string $navigationIcon = "heroicon-o-clipboard-document-list";
    protected static ?string $navigationGroup = "Examinations";
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Attempt Details")
                ->schema([
                    Forms\Components\TextInput::make("uuid")->readOnly(),
                    Forms\Components\Select::make("user_id")->required()->relationship("user", "name")->searchable(),
                    Forms\Components\Select::make("examination_id")->required()->relationship("examination", "title"),
                    Forms\Components\TextInput::make("attempt_number")->integer(),
                    Forms\Components\Select::make("status")
                        ->options(["in_progress" => "In Progress", "completed" => "Completed", "timed_out" => "Timed Out"]),
                    Forms\Components\TextInput::make("total_marks")->numeric(),
                    Forms\Components\TextInput::make("obtained_marks")->numeric(),
                    Forms\Components\TextInput::make("percentage")->numeric(),
                    Forms\Components\Toggle::make("is_passed"),
                    Forms\Components\TextInput::make("time_taken")->integer()->label("Time Taken (seconds)"),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("uuid")->limit(8),
            Tables\Columns\TextColumn::make("user.name")->label("Student"),
            Tables\Columns\TextColumn::make("examination.title")->label("Exam")->limit(30),
            Tables\Columns\TextColumn::make("attempt_number")->label("#"),
            Tables\Columns\BadgeColumn::make("status")
                ->colors(["warning" => "in_progress", "success" => "completed", "danger" => "timed_out"]),
            Tables\Columns\TextColumn::make("obtained_marks")->label("Marks"),
            Tables\Columns\TextColumn::make("percentage")->label("%"),
            Tables\Columns\TextColumn::make("created_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->filters([
              Tables\Filters\SelectFilter::make("user_id")->relationship("user", "name")->label("Student"),
              Tables\Filters\SelectFilter::make("examination_id")->relationship("examination", "title")->label("Exam"),
              Tables\Filters\SelectFilter::make("status")->options(["in_progress" => "In Progress", "completed" => "Completed", "timed_out" => "Timed Out"]),
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
            "index" => Pages\ListExamAttempts::route("/"),
            "create" => Pages\CreateExamAttempt::route("/create"),
            "view" => Pages\ViewExamAttempt::route("/{record}"),
            "edit" => Pages\EditExamAttempt::route("/{record}/edit"),
        ];
    }
}
