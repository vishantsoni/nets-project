<?php
namespace App\Filament\Resources;

use App\Filament\Resources\AIExamGenerationResource\Pages;
use App\Models\AIExamGeneration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AIExamGenerationResource extends Resource
{
    protected static ?string $model = AIExamGeneration::class;
    protected static ?string $navigationIcon = "heroicon-o-sparkles";
    protected static ?string $navigationGroup = "Question Bank";
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("AI Generation")
                ->schema([
                    Forms\Components\Select::make("subject_id")->required()->relationship("subject", "name")->searchable(),
                    Forms\Components\Select::make("topic_id")->relationship("topic", "name")->searchable(),
                    Forms\Components\Select::make("question_type")->required()
                        ->options(["mcq" => "MCQ", "integer" => "Integer", "true_false" => "True/False", "fill_blank" => "Fill in the Blank", "essay" => "Essay"])->default("mcq"),
                    Forms\Components\TextInput::make("count")->integer()->required()->default(10),
                    Forms\Components\Select::make("difficulty")
                        ->options(["easy" => "Easy", "medium" => "Medium", "hard" => "Hard"])->default("medium"),
                    Forms\Components\Textarea::make("prompt")->required()->rows(4),
                    Forms\Components\Select::make("status")
                        ->options(["pending" => "Pending", "generating" => "Generating", "completed" => "Completed", "failed" => "Failed"])->default("pending"),
                    Forms\Components\Textarea::make("generated_questions")->rows(8)->readOnly(),
                    Forms\Components\Textarea::make("error_message")->rows(2)->readOnly(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("subject.name")->label("Subject"),
            Tables\Columns\BadgeColumn::make("question_type"),
            Tables\Columns\TextColumn::make("count"),
            Tables\Columns\TextColumn::make("difficulty"),
            Tables\Columns\BadgeColumn::make("status")
                ->colors(["gray" => "pending", "warning" => "generating", "success" => "completed", "danger" => "failed"]),
            Tables\Columns\TextColumn::make("created_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->filters([
              Tables\Filters\SelectFilter::make("status")->options(["pending" => "Pending", "generating" => "Generating", "completed" => "Completed", "failed" => "Failed"]),
              Tables\Filters\SelectFilter::make("subject_id")->relationship("subject", "name"),
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
            "index" => Pages\ListAIExamGenerations::route("/"),
            "create" => Pages\CreateAIExamGeneration::route("/create"),
            "view" => Pages\ViewAIExamGeneration::route("/{record}"),
            "edit" => Pages\EditAIExamGeneration::route("/{record}/edit"),
        ];
    }
}
