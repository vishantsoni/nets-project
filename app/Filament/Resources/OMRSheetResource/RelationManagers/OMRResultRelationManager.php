<?php
namespace App\Filament\Resources\OMRSheetResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OMRResultRelationManager extends RelationManager
{
    protected static string $relationship = "results";
    protected static ?string $recordLabel = "Result";

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("question.question_text")->limit(40),
            Tables\Columns\TextColumn::make("selected_option"),
            Tables\Columns\IconColumn::make("is_correct")->boolean(),
            Tables\Columns\TextColumn::make("marks_obtained")->money("INR"),
        ]);
    }
}
