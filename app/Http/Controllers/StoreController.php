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
        $query = StudyMaterial::where("is_published", true)->with(["subject", "categories"]);

        // Category filter
        if (request("category")) {
            $query->whereHas("categories", fn ($q) => $q->where("slug", request("category")));
        }

        // Search filter
        if (request("search")) {
            $search = request("search");
            $query->where(function ($q) use ($search) {
                $q->where("title", "like", "%$search%")
                  ->orWhere("description", "like", "%$search%")
                  ->orWhere("description_rich", "like", "%$search%");
            });
        }

        // Sort filter
        $sort = request("sort", "latest");
        switch ($sort) {
            case "price_asc":
                $query->orderBy("price", "asc");
                break;
            case "price_desc":
                $query->orderBy("price", "desc");
                break;
            case "popular":
                $query->orderBy("download_count", "desc");
                break;
            default:
                $query->latest();
        }

        $materials = $query->paginate(12)->withQueryString();

        $categories = Category::with("children")->whereNull("parent_id")->get();
        $featuredCategories = Category::withCount("studyMaterials")
            ->whereHas("studyMaterials", fn($q) => $q->where("is_published", true))
            ->orderBy("study_materials_count", "desc")
            ->limit(4)
            ->get();
        $currentCategory = request("category") ? Category::where("slug", request("category"))->first() : null;

        return view("store", compact("materials", "categories", "featuredCategories", "currentCategory"));
    }

    public function category($category)
    {
        $currentCategory = Category::where("slug", $category)->firstOrFail();

        $query = StudyMaterial::where("is_published", true)
            ->whereHas("categories", fn ($q) => $q->where("slug", $category))
            ->with(["subject", "categories"]);

        // Sort filter
        $sort = request("sort", "latest");
        switch ($sort) {
            case "price_asc":
                $query->orderBy("price", "asc");
                break;
            case "price_desc":
                $query->orderBy("price", "desc");
                break;
            case "popular":
                $query->orderBy("download_count", "desc");
                break;
            default:
                $query->latest();
        }

        $materials = $query->paginate(12)->withQueryString();

        $categories = Category::with("children")->whereNull("parent_id")->get();
        $featuredCategories = Category::withCount("studyMaterials")
            ->whereHas("studyMaterials", fn($q) => $q->where("is_published", true))
            ->orderBy("study_materials_count", "desc")
            ->limit(4)
            ->get();

        return view("store", compact("materials", "categories", "featuredCategories", "currentCategory"));
    }

    public function show($id)
    {
        $material = StudyMaterial::where("is_published", true)
            ->with(["subject", "categories"])
            ->findOrFail($id);

        // Get related materials (same categories, excluding current)
        $categoryIds = $material->categories->pluck("id");
        $relatedMaterials = StudyMaterial::where("is_published", true)
            ->where("study_materials.id", "!=", $id)
            ->whereHas("categories", fn($q) => $q->whereIn("categories.id", $categoryIds))
            ->with(["subject", "categories"])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view("product", compact("material", "relatedMaterials"));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            "material_id" => "required|exists:study_materials,id",
            "quantity" => "required|integer|min:1|max:10",
        ]);

        $material = StudyMaterial::findOrFail($request->material_id);

        if (!Auth::check()) {
            return response()->json(["error" => "Please login first"], 401);
        }

        CartItem::updateOrCreate(
            ["user_id" => Auth::id(), "item_id" => $material->id, "item_type" => "study_material"],
            ["quantity" => $request->quantity, "unit_price" => $material->discount_price ?? $material->price]
        );

        $cartCount = CartItem::where("user_id", Auth::id())->sum("quantity");

        if ($request->expectsJson()) {
            return response()->json(["success" => true, "message" => "Added to cart", "cart_count" => $cartCount]);
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
