<?php
namespace App\Filament\Resources;

use App\Filament\Resources\InstituteResource\Pages;
use App\Models\Institute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InstituteResource extends Resource
{
    protected static ?string $model = Institute::class;
    protected static ?string $navigationIcon = "heroicon-o-building-office-2";
    protected static ?string $navigationGroup = "User Management";
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make("name")->required()->maxLength(255),
                    Forms\Components\TextInput::make("slug")->required()->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make("email")->email(),
                    Forms\Components\TextInput::make("phone"),
                    Forms\Components\TextInput::make("website")->url(),
                ])->columns(2),
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Textarea::make("description")->rows(3),
                    Forms\Components\Textarea::make("address")->rows(2),
                    Forms\Components\FileUpload::make("logo")->image()->directory("institutes"),
                    Forms\Components\Toggle::make("is_active")->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("name")->searchable(),
            Tables\Columns\TextColumn::make("slug")->searchable(),
            Tables\Columns\TextColumn::make("email")->searchable(),
            Tables\Columns\TextColumn::make("phone"),
            Tables\Columns\IconColumn::make("is_active")->boolean(),
            Tables\Columns\TextColumn::make("created_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->filters([Tables\Filters\TernaryFilter::make("is_active")])
          ->actions([
              Tables\Actions\ViewAction::make(),
              Tables\Actions\EditAction::make(),
              Tables\Actions\DeleteAction::make(),
          ])
          ->bulkActions([
              Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
          ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListInstitutes::route("/"),
            "create" => Pages\CreateInstitute::route("/create"),
            "view" => Pages\ViewInstitute::route("/{record}"),
            "edit" => Pages\EditInstitute::route("/{record}/edit"),
        ];
    }
}
