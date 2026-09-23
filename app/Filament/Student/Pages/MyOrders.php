<?php

namespace App\Filament\Student\Pages;

use App\Models\Order;
use Filament\Pages\Page;

class MyOrders extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $title = 'My Orders';
    protected static string $view = 'filament.student.pages.my-orders';
    protected static ?string $navigationGroup = 'E-Commerce';
    protected static ?int $navigationSort = 4;

    public function getOrders()
    {
        $user = auth()->user();

        return Order::where('user_id', $user->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getTotalSpent(): string
    {
        $user = auth()->user();

        $total = Order::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        return number_format($total, 2);
    }
}
