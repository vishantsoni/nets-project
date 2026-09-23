<?php

use App\Http\Controllers\B2BEnquiryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $subjects = \App\Models\Subject::where('is_active', true)->with('topics')->limit(8)->get();
    $exams = \App\Models\Examination::where('is_active', true)->where('is_published', true)
        ->where(function ($q) { $q->whereNull('start_time')->orWhere('start_time', '<=', now()); })
        ->where(function ($q) { $q->whereNull('end_time')->orWhere('end_time', '>=', now()); })
        ->with('subject')->limit(6)->get();
    $materials = \App\Models\StudyMaterial::where('is_published', true)->with('subject')->limit(8)->get();
    $categories = \App\Models\Category::all();

    return view('home', compact('subjects', 'exams', 'materials', 'categories'));
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);
});

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');

Route::get('/b2b-enquiry', [B2BEnquiryController::class, 'create'])->name('b2b-enquiry');
Route::post('/b2b-enquiry', [B2BEnquiryController::class, 'store']);

Route::get('/store', [StoreController::class, 'index'])->name('store');
Route::get('/store/{category}', [StoreController::class, 'category'])->name('store.category');
Route::get('/product/{id}', [StoreController::class, 'show'])->name('product.show');

Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store']);

Route::middleware('auth')->group(function () {
    Route::post('/cart/add', [StoreController::class, 'addToCart'])->name('cart.add');
    Route::get('/cart', [StoreController::class, 'cart'])->name('cart');
    Route::post('/cart/remove', [StoreController::class, 'removeFromCart'])->name('cart.remove');
    Route::get('/checkout', [StoreController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [StoreController::class, 'placeOrder'])->name('checkout.place');
});
