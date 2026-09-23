<?php
namespace App\Filament\Resources;

use App\Filament\Resources\StudyMaterialResource\Pages;
use App\Models\Category;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\Topic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudyMaterialResource extends Resource
{
    protected static ?string $model = StudyMaterial::class;
    protected static ?string $navigationIcon = "heroicon-o-academic-badge";
    protected static ?string $navigationGroup = "Study Materials";
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Details")
                ->schema([
                    Forms\Components\TextInput::make("title")->required()->maxLength(255),
                    Forms\Components\Select::make("type")
                        ->required()
                        ->options(["pdf" => "PDF", "video" => "Video", "document" => "Document", "image" => "Image"])->default("pdf"),
                    Forms\Components\Select::make("subject_id")->relationship("subject", "name")->searchable(),
                    Forms\Components\Select::make("topic_id")->relationship("topic", "name")->searchable(),
                ])->columns(2),
            Forms\Components\Section::make("Media")
                ->schema([
                    Forms\Components\FileUpload::make("file_path")->label("File")->directory("study-materials"),
                    Forms\Components\FileUpload::make("thumbnail")->image()->directory("study-materials/thumbnails"),
                ])->columns(2),
            Forms\Components\Section::make("Pricing & Publishing")
                ->schema([
                    Forms\Components\Toggle::make("is_paid")->default(false),
                    Forms\Components\TextInput::make("price")->numeric()->default(0)->prefix("INR"),
                    Forms\Components\Toggle::make("is_published")->default(false),
                ])->columns(3),
            Forms\Components\Section::make("Categories")
                ->schema([
                    Forms\Components\CheckboxList::make("categories")
                        ->relationship("categories", "name")
                        ->columns(2),
                ]),
            Forms\Components\Section::make("Description")
                ->schema([Forms\Components\Textarea::make("description")->rows(4)]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("title")->searchable(),
            Tables\Columns\BadgeColumn::make("type")->colors(["primary" => "pdf", "success" => "video", "info" => "document", "warning" => "image"]),
            Tables\Columns\TextColumn::make("subject.name")->label("Subject"),
            Tables\Columns\IconColumn::make("is_paid")->boolean()->label("Paid"),
            Tables\Columns\TextColumn::make("price")->money("INR"),
            Tables\Columns\IconColumn::make("is_published")->boolean(),
            Tables\Columns\TextColumn::make("download_count")->label("Downloads"),
            Tables\Columns\TextColumn::make("created_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->filters([
              Tables\Filters\TernaryFilter::make("is_paid"),
              Tables\Filters\TernaryFilter::make("is_published"),
              Tables\Filters\SelectFilter::make("subject_id")->relationship("subject", "name"),
          ])
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
            "index" => Pages\ListStudyMaterials::route("/"),
            "create" => Pages\CreateStudyMaterial::route("/create"),
            "view" => Pages\ViewStudyMaterial::route("/{record}"),
            "edit" => Pages\EditStudyMaterial::route("/{record}/edit"),
        ];
    }
}
