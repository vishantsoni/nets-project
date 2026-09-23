<?php
namespace App\Filament\Resources;

use App\Filament\Resources\PDFExtractionResource\Pages;
use App\Models\PDFExtraction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PDFExtractionResource extends Resource
{
    protected static ?string $model = PDFExtraction::class;
    protected static ?string $navigationIcon = "heroicon-o-document-text";
    protected static ?string $navigationGroup = "Question Bank";
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("PDF Extraction")
                ->schema([
                    Forms\Components\FileUpload::make("file_path")->required()->directory("pdf-extractions")->label("PDF File"),
                    Forms\Components\Select::make("subject_id")->relationship("subject", "name")->searchable(),
                    Forms\Components\Select::make("topic_id")->relationship("topic", "name")->searchable(),
                    Forms\Components\Select::make("status")
                        ->options(["pending" => "Pending", "processing" => "Processing", "completed" => "Completed", "failed" => "Failed"])->default("pending"),
                    Forms\Components\Textarea::make("extracted_content")->rows(6)->readOnly(),
                    Forms\Components\Textarea::make("parsed_questions")->rows(6)->readOnly(),
                    Forms\Components\Textarea::make("error_message")->rows(3)->readOnly(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("file_path")->limit(30),
            Tables\Columns\BadgeColumn::make("status")
                ->colors(["gray" => "pending", "warning" => "processing", "success" => "completed", "danger" => "failed"]),
            Tables\Columns\TextColumn::make("subject.name")->label("Subject"),
            Tables\Columns\TextColumn::make("created_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->filters([Tables\Filters\SelectFilter::make("status")->options(["pending" => "Pending", "processing" => "Processing", "completed" => "Completed", "failed" => "Failed"])])
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
            "index" => Pages\ListPDFExtractions::route("/"),
            "create" => Pages\CreatePDFExtraction::route("/create"),
            "view" => Pages\ViewPDFExtraction::route("/{record}"),
            "edit" => Pages\EditPDFExtraction::route("/{record}/edit"),
        ];
    }
}
