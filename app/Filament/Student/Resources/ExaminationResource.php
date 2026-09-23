<?php
namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\ExaminationResource\Pages;
use App\Filament\Student\Resources\ExaminationResource\RelationManagers;
use App\Models\Examination;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExaminationResource extends Resource
{
    protected static ?string $model = Examination::class;

    protected static ?string $navigationIcon = "heroicon-o-clipboard-document-check";
    protected static ?string $navigationLabel = "Available Exams";
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Examination Details")
                ->schema([
                    Forms\Components\TextInput::make("test_id")->label("Test ID")->readOnly(),
                    Forms\Components\TextInput::make("title")->readOnly(),
                    Forms\Components\Select::make("type")->disabled()
                        ->options(["cbt" => "CBT", "omr" => "OMR", "hybrid" => "Hybrid"]),
                    Forms\Components\TextInput::make("duration")->integer()->readOnly()->label("Duration (minutes)"),
                    Forms\Components\TextInput::make("passing_marks")->integer()->readOnly(),
                    Forms\Components\Toggle::make("negative_marking")->disabled()->label("Negative Marking"),
                    Forms\Components\Toggle::make("shuffle_questions")->disabled(),
                    Forms\Components\Toggle::make("shuffle_options")->disabled(),
                    Forms\Components\Toggle::make("show_result_immediately")->disabled(),
                    Forms\Components\Select::make("result_visibility")
                        ->options(["immediately" => "Immediately", "after_review" => "After Review", "never" => "Never"])->disabled(),
                    Forms\Components\DateTimePicker::make("start_time")->disabled(),
                    Forms\Components\DateTimePicker::make("end_time")->disabled(),
                ])->columns(2),
            Forms\Components\Section::make("Description")
                ->schema([Forms\Components\Textarea::make("description")->rows(4)->readOnly()]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("test_id")->label("Test ID")->searchable(),
            Tables\Columns\TextColumn::make("title")->searchable()->limit(40),
            Tables\Columns\BadgeColumn::make("type")
                ->colors(["primary" => "cbt", "warning" => "omr", "info" => "hybrid"]),
            Tables\Columns\TextColumn::make("duration")->label("Duration (min)"),
            Tables\Columns\TextColumn::make("passing_marks")->label("Passing %"),
            Tables\Columns\TextColumn::make("start_time")->dateTime()->label("Starts At"),
            Tables\Columns\TextColumn::make("end_time")->dateTime()->label("Ends At"),
            Tables\Columns\BadgeColumn::make("is_published")
                ->label("Published")
                ->colors(["success" => true, "danger" => false]),
        ])->defaultSort("created_at", "desc")
          ->filters([
              Tables\Filters\Filter::make("published")
                  ->label("Published Only")
                  ->query(fn (Builder $query) => $query->where("is_published", true)),
              Tables\Filters\SelectFilter::make("type")
                  ->options(["cbt" => "CBT", "omr" => "OMR", "hybrid" => "Hybrid"]),
          ])
          ->actions([
              Tables\Actions\ViewAction::make(),
              Tables\Actions\Action::make("take_exam")
                  ->label("Take Exam")
                  ->color("success")
                  ->icon("heroicon-o-play")
                  ->action(fn (Examination $record) => redirect()->route("student.exams.take", $record->id))
                  ->visible(fn (Examination $record) => $record->is_published && $record->is_active),
          ])
          ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [RelationManagers\ExamSectionRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListExaminations::route("/"),
            "view" => Pages\ViewExamination::route("/{record}"),
            "take" => Pages\TakeExam::route("/{record}/take"),
        ];
    }
}
