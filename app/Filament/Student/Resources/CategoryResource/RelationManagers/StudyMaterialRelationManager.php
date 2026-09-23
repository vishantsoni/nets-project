<?php
namespace App\Filament\Student\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StudyMaterialRelationManager extends RelationManager
{
    protected static string $relationship = "studyMaterials";
    protected static ?string $recordLabel = "Study Material";

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("title")->limit(40),
            Tables\Columns\TextColumn::make("price")->money("INR"),
            Tables\Columns\BadgeColumn::make("is_free")
                ->trueLabel("Free")->falseLabel("Paid"),
        ])->headerActions([])
          ->actions([Tables\Actions\ViewAction::make()]);
    }
}
