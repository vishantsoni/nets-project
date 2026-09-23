<?php
namespace App\Filament\Resources;

use App\Filament\Resources\TopicResource\Pages;
use App\Models\Topic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TopicResource extends Resource
{
    protected static ?string $model = Topic::class;
    protected static ?string $navigationIcon = "heroicon-o-tag";
    protected static ?string $navigationGroup = "Academics";
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Select::make("subject_id")->required()->relationship("subject", "name")->searchable(),
                    Forms\Components\TextInput::make("name")->required()->maxLength(255),
                    Forms\Components\TextInput::make("standard_marks")->integer()->default(1),
                    Forms\Components\TextInput::make("time_allocation")->integer()->helperText("Minutes")->label("Time (min)"),
                    Forms\Components\Toggle::make("is_active")->default(true),
                ])->columns(2),
            Forms\Components\Section::make()
                ->schema([Forms\Components\Textarea::make("description")->rows(3)]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("subject.name")->label("Subject")->searchable(),
            Tables\Columns\TextColumn::make("name")->searchable(),
            Tables\Columns\TextColumn::make("standard_marks"),
            Tables\Columns\IconColumn::make("is_active")->boolean(),
            Tables\Columns\TextColumn::make("created_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->filters([Tables\Filters\SelectFilter::make("subject_id")->relationship("subject", "name")])
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
            "index" => Pages\ListTopics::route("/"),
            "create" => Pages\CreateTopic::route("/create"),
            "view" => Pages\ViewTopic::route("/{record}"),
            "edit" => Pages\EditTopic::route("/{record}/edit"),
        ];
    }
}
