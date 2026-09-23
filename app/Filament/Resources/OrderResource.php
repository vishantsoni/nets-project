<?php
namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = "heroicon-o-shopping-bag";
    protected static ?string $navigationGroup = "E-Commerce";
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Order Information")
                ->schema([
                    Forms\Components\TextInput::make("order_number")->label("Order Number")->readOnly(),
                    Forms\Components\Select::make("user_id")->required()->relationship("user", "name")->searchable(),
                    Forms\Components\TextInput::make("total_amount")->numeric()->prefix("INR"),
                    Forms\Components\TextInput::make("discount_amount")->numeric()->prefix("INR")->default(0),
                    Forms\Components\TextInput::make("tax_amount")->numeric()->prefix("INR")->default(0),
                    Forms\Components\Select::make("currency")->options(["INR" => "INR", "USD" => "USD"])->default("INR"),
                ])->columns(2),
            Forms\Components\Section::make("Status")
                ->schema([
                    Forms\Components\Select::make("status")->required()->options(["pending" => "Pending", "processing" => "Processing", "completed" => "Completed", "cancelled" => "Cancelled", "refunded" => "Refunded"])->default("pending"),
                    Forms\Components\Select::make("payment_status")->required()->options(["pending" => "Pending", "paid" => "Paid", "failed" => "Failed", "refunded" => "Refunded"])->default("pending"),
                    Forms\Components\TextInput::make("payment_method"),
                    Forms\Components\TextInput::make("transaction_id"),
                    Forms\Components\DateTimePicker::make("ordered_at"),
                ])->columns(2),
            Forms\Components\Section::make("Addresses")
                ->schema([
                    Forms\Components\Textarea::make("shipping_address")->rows(3)->label("Shipping Address"),
                    Forms\Components\Textarea::make("billing_address")->rows(3)->label("Billing Address"),
                    Forms\Components\Textarea::make("notes")->rows(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("order_number")->label("Order #")->searchable(),
            Tables\Columns\TextColumn::make("user.name")->label("Customer"),
            Tables\Columns\TextColumn::make("total_amount")->money("INR")->label("Total"),
            Tables\Columns\BadgeColumn::make("status")
                ->colors(["gray" => "pending", "warning" => "processing", "success" => "completed", "danger" => "cancelled", "info" => "refunded"]),
            Tables\Columns\BadgeColumn::make("payment_status")
                ->colors(["gray" => "pending", "success" => "paid", "danger" => "failed"]),
            Tables\Columns\TextColumn::make("ordered_at")->dateTime(),
        ])->defaultSort("created_at", "desc")
          ->filters([
              Tables\Filters\SelectFilter::make("status")
                  ->options(["pending" => "Pending", "processing" => "Processing", "completed" => "Completed", "cancelled" => "Cancelled", "refunded" => "Refunded"]),
              Tables\Filters\SelectFilter::make("payment_status")
                  ->options(["pending" => "Pending", "paid" => "Paid", "failed" => "Failed", "refunded" => "Refunded"]),
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
        return [RelationManagers\OrderItemRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListOrders::route("/"),
            "create" => Pages\CreateOrder::route("/create"),
            "view" => Pages\ViewOrder::route("/{record}"),
            "edit" => Pages\EditOrder::route("/{record}/edit"),
        ];
    }
}
