<?php

namespace App\Filament\Resources\QuestionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionOptionRelationManager extends RelationManager
{
    protected static string $relationship = "options";
    protected static ?string $recordLabel = "Option";

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make("option_text")->required()->rows(2)->label("Option Text"),
            Forms\Components\TextInput::make("option_value")->label("Option Value"),
            Forms\Components\Toggle::make("is_correct")->label("Is Correct?"),
            Forms\Components\TextInput::make("sort_order")->integer()->default(0),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("option_text")->limit(50),
            Tables\Columns\TextColumn::make("option_value"),
            Tables\Columns\IconColumn::make("is_correct")->boolean(),
            Tables\Columns\TextColumn::make("sort_order"),
        ])->headerActions([
            Tables\Actions\CreateAction::make(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }
}
