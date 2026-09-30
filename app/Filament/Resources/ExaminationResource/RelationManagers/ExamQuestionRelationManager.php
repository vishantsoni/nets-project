<?php

namespace App\Filament\Resources\ExaminationResource\RelationManagers;

use App\Models\ExamSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ExamQuestionRelationManager extends RelationManager
{
    protected static string $relationship = "examQuestions";
    protected static ?string $recordLabel = "Question";

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Question Assignment")
                ->schema([
                    Forms\Components\Select::make("question_id")
                        ->label("Question")
                        ->relationship("question", "question_text", function ($query) {
                            return $query->where("status", "published");
                        })
                        ->searchable()
                        ->preload()
                        ->required()
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