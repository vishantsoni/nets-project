<?php
namespace App\Filament\Resources;

use App\Filament\Resources\B2BEnquiryResource\Pages;
use App\Filament\Resources\B2BEnquiryResource\RelationManagers;
use App\Models\B2BEnquiry;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class B2BEnquiryResource extends Resource
{
    protected static ?string $model = B2BEnquiry::class;
    protected static ?string $navigationIcon = "heroicon-o-building-office";
    protected static ?string $navigationGroup = "B2B";
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Enquiry Details")
                ->schema([
                    Forms\Components\TextInput::make("enquiry_number")->label("Enquiry Number")->readOnly(),
                    Forms\Components\TextInput::make("name")->required(),
                    Forms\Components\TextInput::make("email")->email(),
                    Forms\Components\TextInput::make("phone"),
                    Forms\Components\TextInput::make("company"),
                    Forms\Components\TextInput::make("designation"),
                    Forms\Components\TextInput::make("subject")->required(),
                    Forms\Components\Textarea::make("description")->rows(4)->required(),
                ])->columns(2),
            Forms\Components\Section::make("Status & Assignment")
                ->schema([
                    Forms\Components\Select::make("status")
                        ->options(["new" => "New", "in_progress" => "In Progress", "resolved" => "Resolved", "closed" => "Closed"])
                        ->default("new"),
                    Forms\Components\Select::make("assigned_to")
                        ->relationship("assignedTo", "name")
                        ->label("Assigned To"),
                    Forms\Components\DateTimePicker::make("followed_up_at")->label("Last Followed Up"),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("enquiry_number")->label("Enquiry #")->searchable(),
            Tables\Columns\TextColumn::make("name"),
            Tables\Columns\TextColumn::make("company"),
            Tables\Columns\BadgeColumn::make("status")
                ->colors(["gray" => "new", "warning" => "in_progress", "success" => "resolved", "secondary" => "closed"]),
            Tables\Columns\TextColumn::make("assignedTo.name")->label("Assigned To"),
            Tables\Columns\TextColumn::make("created_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->filters([
              Tables\Filters\SelectFilter::make("status")
                  ->options(["new" => "New", "in_progress" => "In Progress", "resolved" => "Resolved", "closed" => "Closed"]),
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
        return [RelationManagers\B2BEnquiryItemRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListB2BEnquiries::route("/"),
            "create" => Pages\CreateB2BEnquiry::route("/create"),
            "view" => Pages\ViewB2BEnquiry::route("/{record}"),
            "edit" => Pages\EditB2BEnquiry::route("/{record}/edit"),
        ];
    }
}
