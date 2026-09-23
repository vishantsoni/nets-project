<?php
namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Institute;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = "heroicon-o-users";
    protected static ?string $navigationGroup = "User Management";
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Personal Information")
                ->schema([
                    Forms\Components\TextInput::make("name")->required()->maxLength(255),
                    Forms\Components\TextInput::make("first_name")->maxLength(255),
                    Forms\Components\TextInput::make("last_name")->maxLength(255),
                    Forms\Components\TextInput::make("email")->required()->email()->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make("phone")->maxLength(255),
                ])->columns(2),
            Forms\Components\Section::make("Role & Access")
                ->schema([
                    Forms\Components\Select::make("role")
                        ->required()
                        ->options(["admin" => "Admin", "institute" => "Coaching Institute", "teacher" => "Teacher", "student" => "Student"])
                        ->default("student"),
                    Forms\Components\Select::make("institute_id")
                        ->relationship("institute", "name")
                        ->label("Institute"),
                    Forms\Components\Toggle::make("is_active")->default(true),
                ]),
            Forms\Components\Section::make("Authentication")
                ->schema([
                    Forms\Components\TextInput::make("password")
                        ->password()->revealable()
                        ->required(fn (string $operation): bool => $operation === "create")
                        ->minLength(8),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("name")->searchable()->sortable(),
            Tables\Columns\TextColumn::make("email")->searchable(),
            Tables\Columns\BadgeColumn::make("role")
                ->colors(["warning" => "institute", "info" => "teacher", "primary" => "student", "success" => "admin"]),
            Tables\Columns\TextColumn::make("institute.name")->label("Institute"),
            Tables\Columns\IconColumn::make("is_active")->boolean(),
            Tables\Columns\TextColumn::make("created_at")->dateTime()->label("Joined"),
        ])->defaultSort("created_at", "desc")
          ->filters([
              Tables\Filters\SelectFilter::make("role")
                  ->options(["admin" => "Admin", "institute" => "Coaching Institute", "teacher" => "Teacher", "student" => "Student"]),
              Tables\Filters\TernaryFilter::make("is_active"),
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
            "index" => Pages\ListUsers::route("/"),
            "create" => Pages\CreateUser::route("/create"),
            "view" => Pages\ViewUser::route("/{record}"),
            "edit" => Pages\EditUser::route("/{record}/edit"),
        ];
    }
}
