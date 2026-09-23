<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ExaminationResource\Pages;
use App\Filament\Resources\ExaminationResource\RelationManagers;
use App\Models\Examination;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExaminationResource extends Resource
{
    protected static ?string $model = Examination::class;
    protected static ?string $navigationIcon = "heroicon-o-clipboard-document-check";
    protected static ?string $navigationGroup = "Examinations";
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Basic Info")
                ->schema([
                    Forms\Components\TextInput::make("title")->required()->maxLength(255),
                    Forms\Components\TextInput::make("test_id")->label("Test ID")->helperText("Auto-generated if left blank"),
                    Forms\Components\Select::make("type")->required()->options(["cbt" => "CBT", "omr" => "OMR", "hybrid" => "Hybrid"])->default("cbt"),
                    Forms\Components\Select::make("subject_id")->relationship("subject", "name")->searchable(),
                ])->columns(2),
            Forms\Components\Section::make("Timing & Settings")
                ->schema([
                    Forms\Components\TextInput::make("duration")->integer()->required()->label("Duration (minutes)"),
                    Forms\Components\TextInput::make("passing_marks")->integer()->label("Passing Marks"),
                    Forms\Components\TextInput::make("max_attempts")->integer()->label("Max Attempts")->default(1),
                    Forms\Components\Toggle::make("negative_marking"),
                    Forms\Components\TextInput::make("negative_marks_ratio")->numeric()->label("Negative Ratio"),
                    Forms\Components\Toggle::make("shuffle_questions")->default(false),
                    Forms\Components\Toggle::make("shuffle_options")->default(false),
                    Forms\Components\Toggle::make("show_result_immediately")->default(true),
                    Forms\Components\Select::make("result_visibility")
                        ->options(["immediately" => "Immediately", "after_review" => "After Review", "never" => "Never"])->default("immediately"),
                    Forms\Components\DateTimePicker::make("start_time"),
                    Forms\Components\DateTimePicker::make("end_time"),
                    Forms\Components\Toggle::make("is_active")->default(false),
                    Forms\Components\Toggle::make("is_published")->default(false),
                ])->columns(2),
            Forms\Components\Section::make("Description")
                ->schema([Forms\Components\Textarea::make("description")->rows(4)]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("test_id")->label("Test ID")->searchable(),
            Tables\Columns\TextColumn::make("title")->searchable(),
            Tables\Columns\BadgeColumn::make("type")->colors(["primary" => "cbt", "warning" => "omr", "info" => "hybrid"]),
            Tables\Columns\TextColumn::make("subject.name")->label("Subject"),
            Tables\Columns\TextColumn::make("duration")->label("Duration (min)"),
            Tables\Columns\IconColumn::make("is_published")->boolean()->label("Published"),
            Tables\Columns\IconColumn::make("is_active")->boolean()->label("Active"),
            Tables\Columns\TextColumn::make("created_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->filters([
              Tables\Filters\TernaryFilter::make("is_published"),
              Tables\Filters\TernaryFilter::make("is_active"),
              Tables\Filters\SelectFilter::make("type")
                  ->options(["cbt" => "CBT", "omr" => "OMR", "hybrid" => "Hybrid"]),
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
        return [RelationManagers\ExamSectionRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListExaminations::route("/"),
            "create" => Pages\CreateExamination::route("/create"),
            "view" => Pages\ViewExamination::route("/{record}"),
            "edit" => Pages\EditExamination::route("/{record}/edit"),
        ];
    }
}
