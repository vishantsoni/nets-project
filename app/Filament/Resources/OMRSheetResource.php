<?php
namespace App\Filament\Resources;

use App\Filament\Resources\OMRSheetResource\Pages;
use App\Filament\Resources\OMRSheetResource\RelationManagers;
use App\Models\OMRSheet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OMRSheetResource extends Resource
{
    protected static ?string $model = OMRSheet::class;
    protected static ?string $navigationIcon = "heroicon-o-camera";
    protected static ?string $navigationGroup = "OMR";
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("OMR Sheet")
                ->schema([
                    Forms\Components\Select::make("examination_id")->required()->relationship("examination", "title"),
                    Forms\Components\Select::make("user_id")->required()->relationship("user", "name"),
                    Forms\Components\FileUpload::make("image_path")->required()->image()->directory("omr-sheets"),
                    Forms\Components\Select::make("status")
                        ->options(["uploaded" => "Uploaded", "processing" => "Processing", "processed" => "Processed"])
                        ->default("uploaded"),
                    Forms\Components\Textarea::make("processed_data")->rows(4)->label("Processed Data")->readOnly(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("examination.title")->label("Exam")->limit(30),
            Tables\Columns\TextColumn::make("user.name")->label("Student"),
            Tables\Columns\BadgeColumn::make("status")
                ->colors(["gray" => "uploaded", "warning" => "processing", "success" => "processed"]),
            Tables\Columns\TextColumn::make("created_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->filters([Tables\Filters\SelectFilter::make("status")->options(["uploaded" => "Uploaded", "processing" => "Processing", "processed" => "Processed"])])
          ->actions([
              Tables\Actions\ViewAction::make(),
              Tables\Actions\EditAction::make(),
              Tables\Actions\DeleteAction::make(),
          ])
          ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [RelationManagers\OMRResultRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListOMRSheets::route("/"),
            "create" => Pages\CreateOMRSheet::route("/create"),
            "view" => Pages\ViewOMRSheet::route("/{record}"),
            "edit" => Pages\EditOMRSheet::route("/{record}/edit"),
        ];
    }
}
