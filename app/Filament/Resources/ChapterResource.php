<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChapterResource\Pages;
use App\Filament\Resources\Concerns\ScopesToInstitute;
use App\Models\Chapter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChapterResource extends Resource
{
    use ScopesToInstitute;

    protected static ?string $model = Chapter::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Academics';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Select::make('subject_id')
                        ->required()
                        ->relationship('subject', 'name')
                        ->searchable(),
                    Forms\Components\TextInput::make('name')->label('Chapter Name')->required()->maxLength(255),
                    Forms\Components\Toggle::make('is_active')->default(true),
                ])->columns(2),
            Forms\Components\Section::make('Description')
                ->schema([
                    Forms\Components\Textarea::make('description')->rows(4),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('subject.name')->label('Subject')->searchable(),
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('created_at')->dateTime(),
        ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('subject_id')
                    ->relationship('subject', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChapters::route('/'),
            'create' => Pages\CreateChapter::route('/create'),
            'view' => Pages\ViewChapter::route('/{record}'),
            'edit' => Pages\EditChapter::route('/{record}/edit'),
        ];
    }
}
