<?php
namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemRelationManager extends RelationManager
{
    protected static string $relationship = "items";
    protected static ?string $recordLabel = "Item";

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make("item_type")->required(),
            Forms\Components\TextInput::make("item_name")->required(),
            Forms\Components\TextInput::make("quantity")->integer()->default(1),
            Forms\Components\TextInput::make("unit_price")->numeric(),
            Forms\Components\TextInput::make("total_price")->numeric(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("item_name"),
            Tables\Columns\TextColumn::make("quantity"),
            Tables\Columns\TextColumn::make("unit_price")->money("INR"),
            Tables\Columns\TextColumn::make("total_price")->money("INR"),
        ])->headerActions([Tables\Actions\CreateAction::make()])
          ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
