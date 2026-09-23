<?php
namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Models\Institute;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;
    protected static ?string $navigationIcon = "heroicon-o-book-open";
    protected static ?string $navigationGroup = "Academics";
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make("name")->required()->maxLength(255),
                    Forms\Components\TextInput::make("code")->unique(ignoreRecord: true),
                    Forms\Components\Select::make("institute_id")
                        ->relationship("institute", "name")
                        ->options(Institute::pluck("name", "id")->toArray())
                        ->label("Institute"),
                    Forms\Components\Toggle::make("is_active")->default(true),
                ])->columns(2),
            Forms\Components\Section::make("Description")
                ->schema([Forms\Components\Textarea::make("description")->rows(4)]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("name")->searchable(),
            Tables\Columns\TextColumn::make("code")->searchable(),
            Tables\Columns\TextColumn::make("institute.name")->label("Institute"),
            Tables\Columns\IconColumn::make("is_active")->boolean(),
            Tables\Columns\TextColumn::make("created_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->filters([
              Tables\Filters\SelectFilter::make("institute_id")
                  ->relationship("institute", "name")->label("Institute"),
          ])
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
            "index" => Pages\ListSubjects::route("/"),
            "create" => Pages\CreateSubject::route("/create"),
            "view" => Pages\ViewSubject::route("/{record}"),
            "edit" => Pages\EditSubject::route("/{record}/edit"),
        ];
    }
}
