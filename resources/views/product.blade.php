@extends('layouts.app')

@section('title', $material->title . ' - NETS')

@section('content')
<main class="bg-gray-50 py-12 sm:py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('store') }}" class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 mb-8">&larr; Back to store</a>
        <div class="grid lg:grid-cols-2 gap-10 bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-10">
            <div class="aspect-[4/3] rounded-xl overflow-hidden bg-gray-100">
                @if($material->thumbnail)
                    <img src="{{ asset('storage/' . $material->thumbnail) }}" alt="{{ $material->title }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300 text-7xl">&#128218;</div>
                @endif
            </div>
            <div class="flex flex-col justify-center">
                @if($material->subject)<p class="text-sm font-semibold uppercase tracking-wide text-indigo-600 mb-3">{{ $material->subject->name }}</p>@endif
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">{{ $material->title }}</h1>
                <p class="mt-5 text-gray-600 leading-7">{{ $material->description ?: 'Build your knowledge with this carefully prepared study resource.' }}</p>
                <div class="mt-8 flex items-center gap-4">
                    @if(!$material->is_paid)<span class="text-2xl font-bold text-green-600">Free</span>@else<span class="text-2xl font-bold text-gray-900">INR {{ number_format($material->price, 2) }}</span>@endif
                    @if($material->type)<span class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-600">{{ ucfirst($material->type) }}</span>@endif
                </div>
                @auth
                    <form method="POST" action="{{ route('cart.add') }}" class="mt-8 flex items-end gap-3">@csrf<input type="hidden" name="material_id" value="{{ $material->id }}"><div><label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity</label><input id="quantity" name="quantity" type="number" min="1" value="1" class="w-24 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></div><button type="submit" class="rounded-lg bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700">Add to cart</button></form>
                @else
                    <a href="{{ route('login') }}" class="mt-8 inline-flex w-fit rounded-lg bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700">Log in to purchase</a>
                @endauth
            </div>
        </div>
    </div>
</main>
@endsection