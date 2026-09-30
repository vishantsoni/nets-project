<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroResource\Pages;
use App\Models\Hero;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HeroResource extends Resource
{
    protected static ?string $model = Hero::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Hero Slide Content')
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('subtitle')
                        ->rows(3)
                        ->maxLength(500),
                ])->columns(2),

            Section::make('Buttons')
                ->schema([
                    TextInput::make('primary_button_text')
                        ->maxLength(100),
                    TextInput::make('primary_button_url')
                        ->maxLength(255)
                        ->placeholder('e.g. /store, https://...'),
                    TextInput::make('secondary_button_text')
                        ->maxLength(100),
                    TextInput::make('secondary_button_url')
                        ->maxLength(255)
                        ->placeholder('e.g. /login, https://...'),
                ])->columns(2),

            Section::make('Background')
                ->schema([
                    FileUpload::make('background_image')
                        ->label('Background Image')
                        ->image()
                        ->directory('heroes')
                        ->visibility('public')
                        ->maxSize(3072),
                    TextInput::make('background_gradient')
                        ->label('Background Gradient (CSS)')
                        ->helperText('e.g. bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500')
                        ->maxLength(255),
                ]),

            Section::make('Settings')
                ->schema([
                    TextInput::make('sort_order')
                        ->label('Sort Order')
                        ->numeric()
                        ->default(0),
                    Toggle::make('is_active')->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('sort_order')->label('Order'),
            TextColumn::make('title')->searchable(),
            TextColumn::make('subtitle')->limit(50),
            ImageColumn::make('background_image'),
            IconColumn::make('is_active')->boolean(),
            TextColumn::make('created_at')->dateTime(),
        ])->defaultSort('sort_order')
            ->reorderable('sort_order')
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
            'index' => Pages\ListHeroes::route('/'),
            'create' => Pages\CreateHero::route('/create'),
            'view' => Pages\ViewHero::route('/{record}'),
            'edit' => Pages\EditHero::route('/{record}/edit'),
        ];
    }
}
