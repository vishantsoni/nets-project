<?php
namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\ScopesToInstitute;
use App\Filament\Resources\TopicResource\Pages;
use App\Models\Chapter;
use App\Models\Subject;
use App\Models\Topic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TopicResource extends Resource
{
    use ScopesToInstitute;

    protected static ?string $model = Topic::class;
    protected static ?string $navigationIcon = "heroicon-o-tag";
    protected static ?string $navigationGroup = "Academics";
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Select::make("subject_id")
                        ->label("Subject")
                        ->required()
                        ->options(fn (): array => Subject::query()
                            ->forInstitute(auth()->user())
                            ->orderBy("name")
                            ->pluck("name", "id")
                            ->all())
                        ->afterStateHydrated(fn (Forms\Components\Select $component, ?Topic $record) => $component->state(
                            $record?->chapter?->subject_id
                        ))
                        ->dehydrated(false)
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set("chapter_id", null))
                        ->searchable(),
                    Forms\Components\Select::make("chapter_id")
                        ->required()
                        ->relationship(
                            "chapter",
                            "name",
                            modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                ->forInstitute(auth()->user())
                                ->where("subject_id", $get("subject_id"))
                        )
                        ->getOptionLabelFromRecordUsing(fn (Chapter $record): string => $record->name)
                        ->disabled(fn (Get $get): bool => blank($get("subject_id")))
                        ->searchable(),
                    Forms\Components\TextInput::make("name")->label("Topic Name")->required()->maxLength(255),
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
            Tables\Columns\TextColumn::make("chapter.subject.name")->label("Subject")->searchable(),
            Tables\Columns\TextColumn::make("chapter.name")->label("Chapter")->searchable(),
            Tables\Columns\TextColumn::make("name")->searchable(),
            Tables\Columns\TextColumn::make("standard_marks"),
            Tables\Columns\IconColumn::make("is_active")->boolean(),
            Tables\Columns\TextColumn::make("created_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->filters([Tables\Filters\SelectFilter::make("chapter_id")->relationship("chapter", "name")])
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
