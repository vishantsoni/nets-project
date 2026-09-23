<?php

namespace App\Filament\Resources\ExaminationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ExamSectionRelationManager extends RelationManager
{
    protected static string $relationship = "sections";
    protected static ?string $recordLabel = "Section";

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make("title")->required(),
            Forms\Components\Textarea::make("description")->rows(3),
            Forms\Components\TextInput::make("question_count")->integer()->default(0),
            Forms\Components\TextInput::make("marks_per_question")->numeric()->default(1),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("title"),
            Tables\Columns\TextColumn::make("question_count"),
            Tables\Columns\TextColumn::make("marks_per_question"),
        ])->headerActions([
            Tables\Actions\CreateAction::make(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }
}
