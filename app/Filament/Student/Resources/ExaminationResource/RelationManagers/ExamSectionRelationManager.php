<?php
namespace App\Filament\Student\Resources\ExaminationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ExamSectionRelationManager extends RelationManager
{
    protected static string $relationship = "sections";
    protected static ?string $recordLabel = "Section";

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("title"),
            Tables\Columns\TextColumn::make("question_count"),
            Tables\Columns\TextColumn::make("marks_per_question"),
        ]);
    }
}
