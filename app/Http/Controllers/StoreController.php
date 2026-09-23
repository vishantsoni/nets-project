<?php

namespace App\Http\Controllers;

use App\Models\StudyMaterial;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class StoreController extends Controller
{
    public function index()
    {
        $materials = StudyMaterial::where("is_published", true)
            ->with("subject")
            ->when(request("category"), function ($q, $cat) {
                $q->whereHas("categories", fn ($q2) => $q2->where("slug", $cat));
            })
            ->when(request("search"), function ($q, $search) {
                $q->where("title", "like", "%$search%")
                  ->orWhere("description", "like", "%$search%");
            })
            ->paginate(12);

        $categories = Category::all();
        $featuredMaterials = StudyMaterial::where("is_published", true)->with("subject")->limit(4)->get();

        return view("store", compact("materials", "categories", "featuredMaterials"));
    }

    public function category($category)
    {
        $materials = StudyMaterial::where("is_published", true)
            ->whereHas("categories", fn ($q) => $q->where("slug", $category))
            ->with("subject")
            ->paginate(12);

        $categories = Category::all();

        return view("store", compact("materials", "categories"));
    }

    public function show($id)
    {
        $material = StudyMaterial::where("is_published", true)->with("subject")->findOrFail($id);

        return view("product", compact("material"));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            "material_id" => "required|exists:study_materials,id",
            "quantity" => "required|integer|min:1",
        ]);

        $material = StudyMaterial::findOrFail($request->material_id);

        if (!Auth::check()) {
            return response()->json(["error" => "Please login first"], 401);
        }

        CartItem::updateOrCreate(
            ["user_id" => Auth::id(), "item_id" => $material->id, "item_type" => "study_material"],
            ["quantity" => $request->quantity, "unit_price" => $material->price]
        );

        if ($request->expectsJson()) {
            return response()->json(["success" => true, "message" => "Added to cart"]);
        }

        return redirect()->route("cart")->with("success", "Added to cart");
    }

    public function cart()
    {
        $cartItems = CartItem::where("user_id", Auth::id())->with("item")->get();

        return view("cart", compact("cartItems"));
    }

    public function removeFromCart(Request $request)
    {
        CartItem::where("user_id", Auth::id())->where("item_id", $request->material_id)->delete();

        if (! $request->expectsJson()) {
            return redirect()->route("cart")->with("success", "Item removed from cart");
        }

        return response()->json(["success" => true]);
    }

    public function checkout()
    {
        $cartItems = CartItem::where("user_id", Auth::id())->with("item")->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route("cart")->with("error", "Your cart is empty");
        }

        $totalAmount = $cartItems->sum(fn ($item) => $item->unit_price * $item->quantity);
        $taxAmount = $totalAmount * 0.18;

        return view("checkout", compact("cartItems", "totalAmount", "taxAmount"));
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            "shipping_address" => "required|string",
            "billing_address" => "required|string",
        ]);

        $cartItems = CartItem::where("user_id", Auth::id())->with("item")->get();
        $totalAmount = $cartItems->sum(fn ($item) => $item->unit_price * $item->quantity);

        $order = Order::create([
            "order_number" => "ORD-" . now()->year . "-" . str_pad(Order::max("id") + 1, 5, "0", STR_PAD_LEFT),
            "user_id" => Auth::id(),
            "total_amount" => $totalAmount,
            "discount_amount" => 0,
            "tax_amount" => $totalAmount * 0.18,
            "currency" => "INR",
            "status" => "pending",
            "payment_status" => "pending",
            "payment_method" => "cod",
            "shipping_address" => $request->shipping_address,
            "billing_address" => $request->billing_address,
            "ordered_at" => now(),
        ]);

        foreach ($cartItems as $item) {
            OrderItem::create([
                "order_id" => $order->id,
                "item_type" => "study_material",
                "item_id" => $item->material->id,
                "item_name" => $item->material->title,
                "quantity" => $item->quantity,
                "unit_price" => $item->unit_price,
                "total_price" => $item->unit_price * $item->quantity,
            ]);
        }

        CartItem::where("user_id", Auth::id())->delete();

        return redirect()->route("home")->with("success", "Order placed successfully!");
    }
}
