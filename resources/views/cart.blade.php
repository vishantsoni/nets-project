@extends('layouts.app')

@section('title', 'Shopping Cart - NETS')

@section('content')
<main class="bg-gray-50 py-12 sm:py-16"><div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8"><h1 class="text-3xl font-bold text-gray-900">Shopping cart</h1>
    @if(session('error'))<div class="mt-6 rounded-lg bg-red-50 p-4 text-red-700">{{ session('error') }}</div>@endif
    @if($cartItems->isEmpty())<div class="mt-8 rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm"><p class="text-lg font-semibold text-gray-900">Your cart is empty</p><p class="mt-2 text-gray-500">Find something useful for your next study session.</p><a href="{{ route('store') }}" class="mt-6 inline-block rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">Browse materials</a></div>
    @else
        @php($subtotal = $cartItems->sum(fn ($item) => $item->unit_price * $item->quantity))
        <div class="mt-8 grid lg:grid-cols-[1fr_20rem] gap-8 items-start"><div class="space-y-4">@foreach($cartItems as $item) @php($material = $item->item) @if($material)<article class="flex gap-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm"><div class="h-24 w-28 shrink-0 overflow-hidden rounded-lg bg-gray-100">@if($material->thumbnail)<img src="{{ asset('storage/' . $material->thumbnail) }}" alt="" class="h-full w-full object-cover">@endif</div><div class="min-w-0 flex-1"><h2 class="font-semibold text-gray-900">{{ $material->title }}</h2><p class="mt-1 text-sm text-gray-500">{{ $item->quantity }} x INR {{ number_format($item->unit_price, 2) }}</p><form method="POST" action="{{ route('cart.remove') }}" class="mt-3">@csrf<input type="hidden" name="material_id" value="{{ $material->id }}"><button class="text-sm font-medium text-red-600 hover:text-red-800">Remove</button></form></div><p class="font-semibold text-gray-900">INR {{ number_format($item->unit_price * $item->quantity, 2) }}</p></article>@endif @endforeach</div><aside class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm"><h2 class="text-lg font-bold text-gray-900">Order summary</h2><div class="mt-5 flex justify-between text-gray-600"><span>Subtotal</span><span>INR {{ number_format($subtotal, 2) }}</span></div><div class="mt-3 flex justify-between text-gray-600"><span>Tax</span><span>18%</span></div><a href="{{ route('checkout') }}" class="mt-6 block rounded-lg bg-indigo-600 px-5 py-3 text-center font-semibold text-white hover:bg-indigo-700">Proceed to checkout</a></aside></div>
    @endif
</div></main>
@endsection