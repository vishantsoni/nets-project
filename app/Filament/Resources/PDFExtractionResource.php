<?php
namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\ScopesToInstitute;
use App\Filament\Resources\PDFExtractionResource\Pages;
use App\Models\PDFExtraction;
use App\Models\Topic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PDFExtractionResource extends Resource
{
    use ScopesToInstitute;

    protected static ?string $model = PDFExtraction::class;
    protected static ?string $navigationIcon = "heroicon-o-document-text";
    protected static ?string $navigationGroup = "Question Bank";
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("PDF Extraction")
                ->schema([
                    Forms\Components\FileUpload::make("file_path")
                        ->required()
                        ->label("PDF File")
                        ->disk("public")
                        ->directory("pdf-extractions")
                        ->acceptedFileTypes(["application/pdf"])
                        ->maxSize(51200)
                        ->helperText("PDF files up to 50 MB are supported."),
                    Forms\Components\Select::make("subject_id")
                        ->required()
                        ->relationship("subject", "name")
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set("topic_id", null)),
                    Forms\Components\Select::make("topic_id")
                        ->relationship(
                            "topic",
                            "name",
                            modifyQueryUsing: fn (Builder $query, Get $get) => $query->whereHas(
                                "chapter",
                                fn (Builder $query) => $query->where("subject_id", $get("subject_id"))
                            )
                        )
                        ->getOptionLabelFromRecordUsing(fn (Topic $record): string => $record->chapter->name . " / " . $record->name)
                        ->disabled(fn (Get $get): bool => blank($get("subject_id")))
                        ->searchable(),
                    Forms\Components\Select::make("status")
                        ->options(["pending" => "Pending", "processing" => "Processing", "completed" => "Completed", "failed" => "Failed"])
                        ->default("pending")
                        ->disabled(),
                    Forms\Components\Textarea::make("extracted_content")
                        ->rows(6)
                        ->readOnly()
                        ->dehydrated(false),
                    Forms\Components\Repeater::make("parsed_questions")
                        ->label("Review questions before import")
                        ->defaultItems(0)
                        ->itemLabel(fn (array $state): ?string => filled($state["question_text"] ?? null)
                            ? \Illuminate\Support\Str::limit($state["question_text"], 70)
                            : "Question")
                        ->schema([
                            Forms\Components\Textarea::make("question_text")
                                ->required()
                                ->rows(3)
                                ->extraInputAttributes(["data-math-input" => true])
                                ->columnSpanFull(),
                            Forms\Components\Repeater::make("options")
                                ->label("Answer options")
                                ->minItems(2)
                                ->schema([
                                    Forms\Components\TextInput::make("option_value")->required()->label("Label"),
                                    Forms\Components\Textarea::make("option_text")
                                        ->required()
                                        ->rows(2)
                                        ->label("Option")
                                        ->extraInputAttributes(["data-math-input" => true]),
                                    Forms\Components\Toggle::make("is_correct")->label("Correct answer"),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                            Forms\Components\Toggle::make("selected")
                                ->label("Include in bulk import")
                                ->default(true),
                            Forms\Components\Textarea::make("explanation")
                                ->rows(2)
                                ->extraInputAttributes(["data-math-input" => true]),
                            Forms\Components\Hidden::make("imported_question_id"),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
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
