<?php
namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\CategoryResource\Pages;
use App\Filament\Student\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = "heroicon-o-tag";
    protected static ?string $navigationLabel = "Categories";
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Category Details")
                ->schema([
                    Forms\Components\TextInput::make("name")->readOnly(),
                    Forms\Components\Textarea::make("description")->rows(3)->readOnly(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("name")->searchable(),
            Tables\Columns\TextColumn::make("studyMaterials.count")->label("Materials"),
            Tables\Columns\TextColumn::make("created_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->actions([Tables\Actions\ViewAction::make()])
          ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [RelationManagers\StudyMaterialRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListCategories::route("/"),
            "view" => Pages\CategoryView::route("/{record}"),
        ];
    }
}
