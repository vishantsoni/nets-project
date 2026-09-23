<?php

namespace App\Filament\Student\Pages;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\StudyMaterial;
use Filament\Pages\Page;

class MyMaterials extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $title = 'My Study Materials';
    protected static string $view = 'filament.student.pages.my-materials';
    protected static ?string $navigationGroup = 'E-Commerce';
    protected static ?int $navigationSort = 3;

    public function getPurchasedMaterials()
    {
        $user = auth()->user();

        return StudyMaterial::where('is_published', true)
            ->whereHas('orderItems', function ($q) use ($user) {
                $q->where('orders.user_id', $user->id)
                    ->where('orders.payment_status', 'paid');
            })
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getFreeMaterials()
    {
        return StudyMaterial::where('is_published', true)
            ->where('is_paid', false)
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPaidMaterials()
    {
        $user = auth()->user();
        $purchasedIds = Order::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.item_type', 'study_material')
            ->pluck('order_items.item_id')
            ->toArray();

        return StudyMaterial::where('is_published', true)
            ->where('is_paid', true)
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($material) use ($purchasedIds) {
                $material->is_purchased = in_array($material->id, $purchasedIds);
                return $material;
            });
    }
}
