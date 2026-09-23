<?php
namespace App\Filament\Resources\B2BEnquiryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class B2BEnquiryItemRelationManager extends RelationManager
{
    protected static string $relationship = "items";
    protected static ?string $recordLabel = "Item";

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make("item_name")->required(),
            Forms\Components\TextInput::make("quantity")->integer()->default(1),
            Forms\Components\Textarea::make("remarks")->rows(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("item_name"),
            Tables\Columns\TextColumn::make("quantity"),
        ])->headerActions([Tables\Actions\CreateAction::make()])
          ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
